<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Service\ApiClient;
use AppBundle\Form\SetPasswordType;
use AppBundle\Form\ChangePasswordType;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Form\UserDetailsBasicType;
use AppBundle\Form\UserDetailsFullType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
* @Route("user")
*/
class UserController extends Controller
{
    /**
     * @Route("/activate/{token}", name="user_activate")
     * @Template()
     */
    public function activateAction(Request $request, $token)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $translator = $this->get('translator');
        
        // check $token is correct
        $user = $apiClient->getEntity('User', 'find_user_by_token', [ 'parameters' => [ 'token' => $token ] ]); /* @var $user User*/
        
        if (!$user->isTokenSentInTheLastHours(User::TOKEN_EXPIRE_HOURS)) {
            throw new \RuntimeException("token expired, require new link");
        }
        
        $formType = new SetPasswordType([
            'passwordMismatchMessage' => $translator->trans('password.validation.passwordMismatch', [], 'user-activate')
        ]);
        $form = $this->createForm($formType, $user);
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                
                // calculated hashed password
                $encodedPassword = $this->get('security.encoder_factory')->getEncoder($user)
                        ->encodePassword($user->getPassword(), $user->getSalt());
                $apiClient->putC('user/' . $user->getId(), json_encode([
                    'password' => $encodedPassword,
                    'active' => true
                ]));
                
                // log in user
                $token = new UsernamePasswordToken($user, null, "secured_area", $user->getRoles());
                $this->get("security.context")->setToken($token); //now the user is logged in
                
                 $this->get('session')->set('_security_secured_area', serialize($token));
                 
                 $request = $this->get("request");
                 $event = new InteractiveLoginEvent($request, $token);
                 $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                 
                 // the following should not be triggered
                 return $this->redirect($this->generateUrl('user_details'));
            }
        } 

        return [
            'token'=>$token, 
            'form' => $form->createView(),
            'isAdmin' => $user->getRole()['role'] === 'ROLE_ADMIN'
        ];
    }
    
    
    /**
     * @Route("/details", name="user_details")
     * @Template()
     */
    public function detailsAction(Request $request)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $apiClient->getEntity('User', 'user/' . $userId); /* @var $user User*/
        $basicFormOnly = $this->get('security.context')->isGranted('ROLE_ADMIN');

        $formType = $basicFormOnly ? new UserDetailsBasicType() : new UserDetailsFullType([
            'addressCountryEmptyValue' => $this->get('translator')->trans('addressCountry.defaultOption', [], 'user-activate'),
        ]);
        $form = $this->createForm($formType, $user);
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $apiClient->putC('user/' . $user->getId(), $form->getData(), [
                    'deserialise_group' => $basicFormOnly ? 'user_details_basic' : 'user_details_full'
                ]);
                
                // after details are added, admin users to go their homepage, deputies go to next step
                return $this->redirect($this->generateUrl($basicFormOnly ? 'admin_homepage' : 'client_add'));
            }
        } else {
            // fill the form in (edit mode)
            $form->setData($user);
        }
        
        return [
            'form' => $form->createView(),
        ];
        
    }
    
    /**
     * @Route("/{action}", name="user_view", defaults={ "action" = ""})
     * @Template()
     **/
    public function indexAction($action)
    {
        $request = $this->getRequest();
        $user = $this->getUser();
        
        $formEditDetails = $this->createForm(new UserDetailsFullType([
            'addressCountryEmptyValue' => 'Please select...', [], 'user_view'
        ]), $user);
        
        $formEditDetails->add('password', new ChangePasswordType(), [ 'error_bubbling' => false, 'mapped' => false ]);
        
        if($request->getMethod() == 'POST'){
            $formEditDetails->handleRequest($request);
            $apiClient = $this->get('apiclient');
           
            if($formEditDetails->isValid()){
                $formData = $formEditDetails->getData();
                $formRawData = $request->request->get('user_details');
                
                /**
                 * if new password has been set then we need to encode this using the encoder and pass it to
                 * the api
                 */
                if(!empty($formRawData['password']['plain_password']['first'])){
                    $encodedPassword = $this->get('security.encoder_factory')->getEncoder($user)
                        ->encodePassword($formRawData['password']['plain_password']['first'], $user->getSalt());
                    $formData->setPassword($encodedPassword);
                    
                    $apiClient->putC('edit_user',$formData, [ 'parameters' => [ 'id' => $user->getId() ]]);
                    
                    return $this->redirect($this->generateUrl('logout'));
                }
                
                $apiClient->putC('edit_user',$formData, [ 'parameters' => [ 'id' => $user->getId() ]]);
                
                return $this->redirect($this->generateUrl('user_view'));
            }
            
        }

        return [
            'action' => $action,
            'user' => $user,
            'formEditDetails' => $formEditDetails->createView()
        ];
    }
    
}