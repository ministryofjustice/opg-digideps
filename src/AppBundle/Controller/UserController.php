<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\ApiClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
* @Route("user")
*/
class UserController extends Controller
{
    /**
     * Landing page to let the user access the app and selecting a password
     * 
     * Used for both user activation (Step1) or password reset. The controller logic is very similar
     * 
     * @Route("/{action}/{token}", name="user_activate", defaults={ "action" = "activate"}, requirements={
     *   "action" = "(activate|password-reset)"
     * })
     * @Template()
     */
    public function activateUserAction(Request $request, $action, $token)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $translator = $this->get('translator');
        
        // check $token is correct
        try {
            $user = $this->get('userService')->loadUserByToken($token); /* @var $user EntityDir\User*/
        } catch (\Exception $e) {
            throw new \AppBundle\Exception\DisplayableException('Token already used or invalid.');
        }
        
        if (!$user->isTokenSentInTheLastHours(EntityDir\User::TOKEN_EXPIRE_HOURS)) {
            switch ($action) {
                case 'activate':
                    return $this->render('AppBundle:User:activateTokenExpired.html.twig', [
                        'token'=>$token, 
                        'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
                    ]);
                    
                case 'password-reset':
                    return $this->render('AppBundle:User:passwordResetTokenExpired.html.twig', [
                        'token'=>$token, 
                        'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
                    ]);
            }
        }
        
        // define form and template that differs depending on the action (activate or password-reset)
        switch ($action) {
            case 'activate':
                $formType = new FormDir\SetPasswordType([
                    'passwordMismatchMessage' => $translator->trans('password.validation.passwordMismatch', [], 'user-activate')
                ]);
                $template = 'AppBundle:User:activate.html.twig';
                $email = 'activate';
                break;
            
            case 'password-reset':
                $formType = new FormDir\ResetPasswordType([
                    'passwordMismatchMessage' => $this->get('translator')->trans('password.validation.passwordMismatch', [], 'password-reset')
                ]);
                $template = 'AppBundle:User:passwordReset.html.twig';
                $email = 'password-reset';
                break;
            
            default:
                return $this->createNotFoundException("action $action not defined ");
        }
        
        $form = $this->createForm($formType, $user);
        
        $form->handleRequest($request);
        if ($form->isValid()) {

            // calculated hashed password
            $encodedPassword = $this->get('security.encoder_factory')->getEncoder($user)
                    ->encodePassword($user->getPassword(), $user->getSalt());

            $apiClient->putC('user/' . $user->getId() . '/set-password', json_encode([
                'password' => $encodedPassword,
                'set_active' => true,
                'send_email' => false //not sent on this "landing" pages
            ]));

            // log in user
            $token = new UsernamePasswordToken($user, null, "secured_area", $user->getRoles());
            $this->get("security.context")->setToken($token); //now the user is logged in

            $session = $this->get('session');
            $session->set('_security_secured_area', serialize($token));

             $request = $this->get("request");
             $event = new InteractiveLoginEvent($request, $token);
             $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

             // the following should not be triggered
             return $this->redirect($this->generateUrl('user_details'));
        }

        return $this->render($template, [
            'token'=>$token, 
            'form' => $form->createView(),
            'isAdmin' => $user->getRole()['role'] === 'ROLE_ADMIN'
        ]);
    }
    
    /**
     * @Route("/activate/password/send/{token}", name="activation_link_send")
     * @Template()
     */
    public function activateLinkSendAction(Request $request, $token)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        // check $token is correct
        $user = $this->get('userService')->loadUserByToken($token); /* @var $user EntityDir\User*/
        
        // recreate token
        // the endpoint will also send the activation email
        $apiClient->putC('user/' .  $user->getId() . '/recreate-token/email/activate', $user);
        
//        $activationEmail = $this->get('mailFactory')->createActivationEmail($user);
//        $this->get('mailSender')->send($activationEmail, [ 'text', 'html']);
        
        return $this->redirect($this->generateUrl('activation_link_sent', ['token'=>$token]));
    }
    
     /**
     * @Route("/activate/password/sent/{token}", name="activation_link_sent")
     * @Template()
     */
    public function activateLinkSentAction(Request $request, $token)
    {
        return [
            'token'=>$token,
            'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
        ];
    }
    
    /**
     * Registration steps
     *
     * @Route("/details", name="user_details")
     * @Template()
     */
    public function detailsAction(Request $request)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $apiClient->getEntity('User', 'user/' . $userId); /* @var $user EntityDir\User*/
        $basicFormOnly = $this->get('security.context')->isGranted('ROLE_ADMIN');
        $notification = $request->query->has('notification')? $request->query->get('notification'): null;

        $formType = $basicFormOnly ? new FormDir\UserDetailsBasicType() : new FormDir\UserDetailsFullType([
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
            'form' => $form->createView()
        ];
        
    }
    
    /**
     * change user
     * 
     * @Route("/{action}", name="user_view", defaults={ "action" = ""})
     * @Template()
     **/
    public function indexAction($action)
    {
        $request = $this->getRequest();
        $user = $this->getUser();
        
        $basicFormOnly = $this->get('security.context')->isGranted('ROLE_ADMIN');
        $formType = $basicFormOnly ? new FormDir\UserDetailsBasicType() : new FormDir\UserDetailsFullType([
            'addressCountryEmptyValue' => $this->get('translator')->trans('addressCountry.defaultOption', [], 'user-details'),
        ]);
        
        $formEditDetails = $this->createForm($formType, $user);
        
        $formEditDetails->add('password', new FormDir\ChangePasswordType($request), [ 'error_bubbling' => false, 'mapped' => false ]);
        
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

                $apiClient->putC('user/' . $user->getId() . '/set-password', json_encode([
                    'password' => $encodedPassword,
                    'send_email' => true
                ]));
                
                $request->getSession()->getFlashBag()->add(
                    'notification',
                    'page.passwordChangedNotification'
                );

            }
            
            $apiClient->putC('user/' . $user->getId(), $formData);

            return $this->redirect($this->generateUrl('user_view'));
        }
            

        return [
            'action' => $action,
            'user' => $user,
            'formEditDetails' => $formEditDetails->createView()
        ];
    }
    
     /**
     * @Route("/password/forgotten", name="password_forgotten")
     * @Template()
     **/
    public function passwordForgottenAction(Request $request)
    {
        $user = new EntityDir\User;
        $form = $this->createForm(new FormDir\PasswordForgottenType(), $user);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $apiClient = $this->get('apiclient');
                /* @var $user EntityDir\User */
                $user = $this->get('deputyprovider')->loadUserByUsername($form->getData()->getEmail());
                $apiClient->putC('user/' .  $user->getId() . '/recreate-token/email/pass-reset', $user);
                
            } catch (\Exception $e) {
                $this->get('logger')->debug($e->getMessage());
            }

            // after details are added, admin users to go their homepage, deputies go to next step
            return $this->redirect($this->generateUrl('password_sent'));
        }
        
        return [
            'form' => $form->createView()
        ];
    }
    
    /**
     * @Route("/password/sent", name="password_sent")
     * @Template()
     **/
    public function passwordSentAction()
    {
        return [];
    }
    
   
    
}