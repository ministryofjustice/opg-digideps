<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserController extends AbstractController
{
    /**
     * Landing page to let the user access the app and selecting a password.
     * 
     * Used for both user activation (Step1) or password reset. The controller logic is very similar
     * 
     * @Route("/user/{action}/{token}", name="user_activate", defaults={ "action" = "activate"}, requirements={
     *   "action" = "(activate|password-reset)"
     * })
     * @Template()
     */
    public function activateUserAction(Request $request, $action, $token)
    {
        $translator = $this->get('translator');

        // check $token is correct
        try {
            $user = $this->getRestClient()->loadUserByToken($token); /* @var $user EntityDir\User*/
        } catch (\Exception $e) {
            throw new \AppBundle\Exception\DisplayableException('This link is not working or has already been used');
        }

        if (!$user->isTokenSentInTheLastHours(EntityDir\User::TOKEN_EXPIRE_HOURS)) {
            if ('activate' == $action) {
                return $this->render('AppBundle:User:activateTokenExpired.html.twig', [
                    'token' => $token,
                    'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
                ]);
            } else { // password-reset
                return $this->render('AppBundle:User:passwordResetTokenExpired.html.twig', [
                    'token' => $token,
                    'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
                ]);
            }
        }

        // define form and template that differs depending on the action (activate or password-reset)
        if ('activate' == $action) {
            $formType = new FormDir\SetPasswordType([
                'passwordMismatchMessage' => $translator->trans('password.validation.passwordMismatch', [], 'user-activate'),
            ]);
            $template = 'AppBundle:User:activate.html.twig';
        } else { // 'password-reset'
            $formType = new FormDir\ResetPasswordType([
                'passwordMismatchMessage' => $this->get('translator')->trans('form.password.validation.passwordMismatch', [], 'password-reset'),
            ]);
            $template = 'AppBundle:User:passwordReset.html.twig';
        }

        $form = $this->createForm($formType, $user);

        $form->handleRequest($request);
        if ($form->isValid()) {

            // login user into API
            //TODO try move at the beginning
            $this->get('deputyprovider')->login(['token' => $token]);

            // set password for user
            $this->getRestClient()->put('user/'.$user->getId().'/set-password', json_encode([
                'password_plain' => $user->getPassword(),
                'set_active' => true,
            ]));

            // log in user into CLIENT
            $clientToken = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->get('security.context')->setToken($clientToken); //now the user is logged in

            $session = $this->get('session');
            $session->set('_security_secured_area', serialize($clientToken));

             //$request = $this->get("request");
             //$event = new InteractiveLoginEvent($request, $clientToken);
             //$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

            // after password reset
            if ($action == 'password-reset' /*|| $this->get('security.context')->isGranted('ROLE_ADMIN') || $this->get('security.context')->isGranted('ROLE_AD')*/) {
                $redirectUrl = $this->get('redirectorService')->getFirstPageAfterLogin();
            } else { // activate:  o to 2nd step
                $redirectUrl = $this->generateUrl('user_details');
            }

             // the following should not be triggered
            return $this->redirect($redirectUrl);
        }

//             $email = $this->getMailFactory()->createChangePasswordEmail($user);
//            $this->getMailSender()->send($email, ['html']);

        return $this->render($template, [
            'token' => $token,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/user/activate/password/send/{token}", name="activation_link_send")
     * @Template()
     */
    public function activateLinkSendAction(Request $request, $token)
    {
        // check $token is correct
        $user = $this->getRestClient()->loadUserByToken($token); /* @var $user EntityDir\User*/

        // recreate token
        // the endpoint will also send the activation email
        $this->getRestClient()->userRecreateToken($user->getEmail(), 'activate');

        $activationEmail = $this->getMailFactory()->createActivationEmail($user);
        $this->getMailSender()->send($activationEmail, ['text', 'html']);

        return $this->redirect($this->generateUrl('activation_link_sent', ['token' => $token]));
    }

    /**
     * @Route("/user/activate/password/sent/{token}", name="activation_link_sent")
     * @Template()
     */
    public function activateLinkSentAction(Request $request, $token)
    {
        return [
            'token' => $token,
            'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
        ];
    }

    /**
     * Registration steps.
     *
     * @Route("/user/details", name="user_details")
     * @Template()
     */
    public function detailsAction(Request $request)
    {
        $user = $this->getUserWithData(['user', 'role']);

        $basicFormOnly = $this->get('security.context')->isGranted('ROLE_ADMIN') ||  $this->get('security.context')->isGranted('ROLE_AD');
        $notification = $request->query->has('notification') ? $request->query->get('notification') : null;

        $formType = $basicFormOnly ? new FormDir\UserDetailsBasicType() : new FormDir\UserDetailsFullType([
            'addressCountryEmptyValue' => $this->get('translator')->trans('addressCountry.defaultOption', [], 'user-activate'),
        ]);
        $form = $this->createForm($formType, $user);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getRestClient()->put('user/'.$user->getId(), $form->getData(), [
                    $basicFormOnly ? 'user_details_basic' : 'user_details_full',
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
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user-account/password-edit", name="user_password_edit")
     * @Template()
     */
    public function passwordEditAction(Request $request)
    {
        $user = $this->getUserWithData(['user', 'role', 'client']);
        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;

        $form = $this->createForm(new FormDir\ChangePasswordType(), $user, ['mapped' => false, 'error_bubbling' => true]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $plainPassword = $request->request->get('change_password')['plain_password']['first'];
            $this->getRestClient()->put('user/'.$user->getId().'/set-password', json_encode([
                'password_plain' => $plainPassword,
            ]));

            return $this->redirect($this->generateUrl('user_password_edit_done'));
        }

        return [
            'client' => $client,
            'user' => $user,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user-account/password-edit-done", name="user_password_edit_done")
     * @Template()
     */
    public function passwordEditDoneAction(Request $request)
    {
        $user = $this->getUserWithData(['user', 'role', 'client']);
        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;

        return [
            'client' => $client,
        ];
    }

    /**
     * - change user data
     * - chang user password.
     * 
     * @Route("/user-account/user-show", name="user_show")
     * @Template()
     **/
    public function showAction()
    {
        $user = $this->getUserWithData(['user', 'role', 'client']);
        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;

        return [
            'client' => $client,
            'user' => $user,
        ];
    }

    /**
     * - change user data
     * - chang user password.
     * 
     * @Route("/user-account/user-edit", name="user_edit")
     * @Template()
     **/
    public function editAction(Request $request)
    {
        $user = $this->getUserWithData(['user', 'client', 'role']);

        $basicFormOnly = $this->get('security.context')->isGranted('ROLE_ADMIN') || $this->get('security.context')->isGranted('ROLE_AD');
        $formType = $basicFormOnly ? new FormDir\UserDetailsBasicType() : new FormDir\UserDetailsFullType([
            'addressCountryEmptyValue' => $this->get('translator')->trans('addressCountry.defaultOption', [], 'user-details'),
        ]);

        $form = $this->createForm($formType, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();
            /*
             * if new password has been set then we need to encode this using the encoder and pass it to
             * the api
             */
            $this->getRestClient()->put('user/'.$user->getId(), $formData, ['user_details_full']);

            return $this->redirect($this->generateUrl('user_show'));
        }

        $client = !empty($user->getClients()) ? $user->getClients()[0] : null;

        return [
            'client' => $client,
            'user' => $user,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/password-managing/forgotten", name="password_forgotten")
     * @Template()
     **/
    public function passwordForgottenAction(Request $request)
    {
        $user = new EntityDir\User();
        $form = $this->createForm(new FormDir\PasswordForgottenType(), $user);

        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                /* @var $user EntityDir\User */
                $user = $this->getRestClient()->userRecreateToken($user->getEmail(), 'pass-reset');
                $resetPasswordEmail = $this->getMailFactory()->createResetPasswordEmail($user);

                $this->getMailSender()->send($resetPasswordEmail, ['text', 'html']);
            } catch (\Exception $e) {
                $this->get('logger')->debug($e->getMessage());
            }

            // after details are added, admin users to go their homepage, deputies go to next step
            return $this->redirect($this->generateUrl('password_sent'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/password-managing/sent", name="password_sent")
     * @Template()
     **/
    public function passwordSentAction()
    {
        return [];
    }
}
