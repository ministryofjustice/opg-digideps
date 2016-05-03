<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
* @Route("user")
*/
class UserController extends AbstractController
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
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $translator = $this->get('translator');
        
        // check $token is correct
        try {
            $user = $this->get('restClient')->loadUserByToken($token); /* @var $user EntityDir\User*/
        } catch (\Exception $e) {
            throw new \AppBundle\Exception\DisplayableException('This link is not working or has already been used');
        }
        
        if (!$user->isTokenSentInTheLastHours(EntityDir\User::TOKEN_EXPIRE_HOURS)) {
            if ('activate' == $action) {
                return $this->render('AppBundle:User:activateTokenExpired.html.twig', [
                    'token'=>$token, 
                    'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
                ]);
            } else { // password-reset
                return $this->render('AppBundle:User:passwordResetTokenExpired.html.twig', [
                    'token'=>$token, 
                    'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
                ]);
            }
        }
        
        // define form and template that differs depending on the action (activate or password-reset)
        if ('activate' == $action) {
            $formType = new FormDir\SetPasswordType([
                'passwordMismatchMessage' => $translator->trans('password.validation.passwordMismatch', [], 'user-activate')
            ]);
            $template = 'AppBundle:User:activate.html.twig';
        } else { // 'password-reset'
            $formType = new FormDir\ResetPasswordType([
                'passwordMismatchMessage' => $this->get('translator')->trans('password.validation.passwordMismatch', [], 'password-reset')
            ]);
            $template = 'AppBundle:User:passwordReset.html.twig';
        }
        
        $form = $this->createForm($formType, $user);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            
            // login user into API
            //TODO try move at the beginning
            $this->get('deputyprovider')->login(['token'=>$token]);
            
            // set password for user
            $restClient->put('user/' . $user->getId() . '/set-password', json_encode([
                'password_plain' => $user->getPassword(),
                'set_active' => true,
                'send_email' => false //not sent on this "landing" pages
            ]));

            // log in user into CLIENT
            $clientToken = new UsernamePasswordToken($user, null, "secured_area", $user->getRoles());
            $this->get("security.context")->setToken($clientToken); //now the user is logged in

            $session = $this->get('session');
            $session->set('_security_secured_area', serialize($clientToken));

             //$request = $this->get("request");
             //$event = new InteractiveLoginEvent($request, $clientToken);
             //$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            // after password reset
            if ($action == 'password-reset' /*|| $this->get('security.context')->isGranted('ROLE_ADMIN') || $this->get('security.context')->isGranted('ROLE_AD')*/) {
                $redirectUrl = $this->get('redirectorService')->getFirstPageAfterLogin(false);
            } else { // activate:  o to 2nd step
                $redirectUrl = $this->generateUrl('user_details');
            }
            
             // the following should not be triggered
            return $this->redirect($redirectUrl);
        }

        return $this->render($template, [
            'token'=>$token, 
            'form' => $form->createView(),
            'userRole' => $user->getRole()['role']
        ]);
    }
    
    /**
     * @Route("/activate/password/send/{token}", name="activation_link_send")
     * @Template()
     */
    public function activateLinkSendAction(Request $request, $token)
    {
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        
        // check $token is correct
        $user = $this->get('restClient')->loadUserByToken($token); /* @var $user EntityDir\User*/
        
        // recreate token
        // the endpoint will also send the activation email
        $restClient->userRecreateToken($user, 'activate');
        
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
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $restClient->get('user/' . $userId, 'User'); /* @var $user EntityDir\User*/
        $basicFormOnly = $this->get('security.context')->isGranted('ROLE_ADMIN') ||  $this->get('security.context')->isGranted('ROLE_AD');
        $notification = $request->query->has('notification')? $request->query->get('notification'): null;

        $formType = $basicFormOnly ? new FormDir\UserDetailsBasicType() : new FormDir\UserDetailsFullType([
            'addressCountryEmptyValue' => $this->get('translator')->trans('addressCountry.defaultOption', [], 'user-activate'),
        ]);
        $form = $this->createForm($formType, $user);
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $restClient->put('user/' . $user->getId(), $form->getData(), [
                    'deserialise_group' => $basicFormOnly ? 'user_details_basic' : 'user_details_full'
                ]);
                
                if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                    $route = 'admin_homepage';
                } elseif ($this->get('security.context')->isGranted('ROLE_AD')) {
                    $route = 'ad_homepage';
                } else {
                    $route = 'client_add';
                }
                
                // after details are added, admin users to go their homepage, deputies go to next step
                return $this->redirect($this->generateUrl($route));
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
     * Registration steps
     *
     * @Route("/user-account/password", name="user_password_edit")
     * @Template()
     */
    public function passwordEditAction(Request $request)
    {
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $user = $restClient->get('user/' . $userId, 'User'); /* @var $user EntityDir\User*/
        $basicFormOnly = $this->get('security.context')->isGranted('ROLE_ADMIN') ||  $this->get('security.context')->isGranted('ROLE_AD');
        $notification = $request->query->has('notification')? $request->query->get('notification'): null;

        $formType = $basicFormOnly ? new FormDir\UserDetailsBasicType() : new FormDir\UserDetailsFullType([
            'addressCountryEmptyValue' => $this->get('translator')->trans('addressCountry.defaultOption', [], 'user-activate'),
        ]);
        $form = $this->createForm($formType, $user);
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $restClient->put('user/' . $user->getId(), $form->getData(), [
                    'deserialise_group' => $basicFormOnly ? 'user_details_basic' : 'user_details_full'
                ]);
                
                if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                    $route = 'admin_homepage';
                } elseif ($this->get('security.context')->isGranted('ROLE_AD')) {
                    $route = 'ad_homepage';
                } else {
                    $route = 'client_add';
                }
                
                // after details are added, admin users to go their homepage, deputies go to next step
                return $this->redirect($this->generateUrl($route));
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
     * - change user data
     * - chang user password
     * 
     * @Route("/{action}", name="user_view", defaults={ "action" = ""})
     * @Template()
     **/
    public function indexAction($action)
    {
        $request = $this->getRequest();
        $user = $this->getUser();
        
        $basicFormOnly = $this->get('security.context')->isGranted('ROLE_ADMIN') || $this->get('security.context')->isGranted('ROLE_AD');
        $formType = $basicFormOnly ? new FormDir\UserDetailsBasicType() : new FormDir\UserDetailsFullType([
            'addressCountryEmptyValue' => $this->get('translator')->trans('addressCountry.defaultOption', [], 'user-details'),
        ]);
        
        $formEditDetails = $this->createForm($formType, $user);
        
        $formEditDetails->add('password', new FormDir\ChangePasswordType($request), [ 'error_bubbling' => false, 'mapped' => false ]);
        
        $formEditDetails->handleRequest($request);
        $restClient = $this->get('restClient');

        if($formEditDetails->isValid()){
            $formData = $formEditDetails->getData();
            $formRawData = $request->request->get('user_details');
            /**
             * if new password has been set then we need to encode this using the encoder and pass it to
             * the api
             */
            if (!empty($formRawData['password']['plain_password']['first'])){
                $restClient->put('user/' . $user->getId() . '/set-password', json_encode([
                    'password_plain' => $formRawData['password']['plain_password']['first'],
                    'send_email' => true
                ]));
                
                $request->getSession()->getFlashBag()->add(
                    'notification',
                    'page.passwordChangedNotification'
                );

            }
            
            $restClient->put('user/' . $user->getId(), $formData);

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
                $restClient = $this->get('restClient');
                /* @var $user EntityDir\User */
                $restClient->userRecreateToken($user, 'pass-reset');

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