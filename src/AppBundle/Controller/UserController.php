<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Service\ApiClient;
use AppBundle\Form\SetPasswordType;
use AppBundle\Form\UserDetailsType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
* @Route("user")
*/
class UserController extends Controller
{
    /**
     * @Route("/activate/{token}", name="user_activate")
     */
    public function activateAction(Request $request, $token)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $translator = $this->get('translator');
        
        // check $token is correct
        $user = $apiClient->getEntity('User', 'find_user_by_token', [ 'query' => [ 'token' => $token ] ]); /* @var $user User*/
        
        if (!$user->isTokenSentInTheLastHours(48)) {
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
                 //$request = $this->get("request");
                 //$event = new InteractiveLoginEvent($request, $token);
                 //$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                
                // redirect to step 2
                 if($this->get('security.context')->isGranted('ROLE_ADMIN')){
                    return $this->redirect($this->generateUrl('admin_homepage'));
                //if a lay deputy    
                }elseif($this->get('security.context')->isGranted('ROLE_LAY_DEPUTY')){
                    return $this->redirect($this->generateUrl('user_details'));
                //if no role throw exception    
                }else{
                    throw new AccessDeniedException();  
                }
            }
        } 
        
        return $this->render('AppBundle:User:activate.html.twig', [
            'token'=>$token, 
            'form' => $form->createView()
        ]);
    }
    
    
    /**
     * @Route("/details", name="user_details")
     */
    public function detailsAction(Request $request)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $user = $this->get('security.context')->getToken()->getUser();

        $formType = new UserDetailsType();
        $form = $this->createForm($formType, $user);
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $apiClient->putC('user/' . $user->getId(), $form->getData());
                
                return $this->redirect($this->generateUrl('client_details'));
            }
        } 
        
        return $this->render('AppBundle:User:details.html.twig', [
             'form' => $form->createView()
        ]);
    }
}