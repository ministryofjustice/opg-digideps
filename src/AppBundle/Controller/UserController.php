<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Model\SelfRegisterData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
        $isActivatePage = 'activate' === $action;

        // check $token is correct
        try {
            $user = $this->getRestClient()->loadUserByToken($token);
            /* @var $user EntityDir\User */
        } catch (\Exception $e) {
            throw new \AppBundle\Exception\DisplayableException('This link is not working or has already been used');
        }

        // token expired
        if (!$user->isTokenSentInTheLastHours(EntityDir\User::TOKEN_EXPIRE_HOURS)) {
            $template = $isActivatePage ? 'AppBundle:User:activateTokenExpired.html.twig' : 'AppBundle:User:passwordResetTokenExpired.html.twig';

            return $this->render($template, [
                'token'            => $token,
                'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
            ]);
        }

        // PA must agree to terms before activating the account
        // this check happens before activating the account, therefore no need to set an ACL on all the actions
        if ($isActivatePage && $user->getRoleName() == EntityDir\User::ROLE_PA && !$user->getAgreeTermsUse()) {
            return $this->redirectToRoute('user_agree_terms_use', ['token' => $token]);
        }

        // define form and template that differs depending on the action (activate or password-reset)
        if ($isActivatePage) {
            $passwordMismatchMessage = $translator->trans('password.validation.passwordMismatch', [], 'user-activate');
            $form = $this->createForm(new FormDir\SetPasswordType([
                'passwordMismatchMessage' => $passwordMismatchMessage,
            ]), $user);
            $template = 'AppBundle:User:activate.html.twig';
        } else { // 'password-reset'
            $passwordMismatchMessage = $translator->trans('form.password.validation.passwordMismatch', [], 'password-reset');
            $form = $this->createForm(new FormDir\ResetPasswordType([
                'passwordMismatchMessage' => $passwordMismatchMessage,
            ]), $user);
            $template = 'AppBundle:User:passwordReset.html.twig';
        }

        $form->handleRequest($request);
        if ($form->isValid()) {

            // login user into API
            $this->get('deputy_provider')->login(['token' => $token]);

            // set password for user
            $this->getRestClient()->put('user/' . $user->getId() . '/set-password', json_encode([
                'password_plain' => $user->getPassword(),
                'set_active'     => true,
            ]));

            // log in
            $clientToken = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->get('security.context')->setToken($clientToken); //now the user is logged in

            $session = $this->get('session');
            $session->set('_security_secured_area', serialize($clientToken));

            $redirectUrl = $isActivatePage
                ? $this->generateUrl('user_details')
                : $this->get('redirector_service')->getFirstPageAfterLogin();

            return $this->redirect($redirectUrl);
        }

        return $this->render($template, [
            'token' => $token,
            'form'  => $form->createView(),
            'user'  => $user,
        ]);
    }

    /**
     * @Route("/user/activate/password/send/{token}", name="activation_link_send")
     * @Template()
     */
    public function activateLinkSendAction(Request $request, $token)
    {
        // check $token is correct
        $user = $this->getRestClient()->loadUserByToken($token);
        /* @var $user EntityDir\User */

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
            'token'            => $token,
            'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
        ];
    }

    /**
     * Page to edit user details.
     * For :
     * - admin
     * - AD
     * - Lay
     * - PA
     *
     * @Route("/user/details", name="user_details")
     * @Template()
     */
    public function detailsAction(Request $request)
    {
        $user = $this->getUserWithData();

        list($formType, $jmsPutGroups) = $this->getFormAndJmsGroupBasedOnUserRole($user);
        $form = $this->createForm($formType, $user);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getRestClient()->put('user/' . $user->getId(), $form->getData(), $jmsPutGroups);

            return $this->redirect($this->generateUrl([
                EntityDir\User::ROLE_ADMIN          => 'admin_homepage',
                EntityDir\User::ROLE_AD             => 'ad_homepage',
                EntityDir\User::ROLE_PA             => 'pa_dashboard',
                EntityDir\User::ROLE_PA_ADMIN       => 'pa_dashboard',
                EntityDir\User::ROLE_PA_TEAM_MEMBER => 'pa_dashboard',
                EntityDir\User::ROLE_LAY_DEPUTY     => 'client_add',
            ][$user->getRoleName()]));
        }

        return [
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

    /**
     * @Route("/register", name="register")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $selfRegisterData = new SelfRegisterData();
        $form = $this->createForm(new FormDir\SelfRegisterDataType(), $selfRegisterData);
        $translator = $this->get('translator');
        $vars = [];

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            try {
                $user = $this->getRestClient()->registerUser($data);
                $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                $this->getMailSender()->send($activationEmail);

                $bodyText = $translator->trans('thankyou.body', [], 'register');
                $email = $data->getEmail();
                $bodyText = str_replace('{{ email }}', $email, $bodyText);

                $signInText = $translator->trans('signin', [], 'register');
                $signIn = '<a href="' . $this->generateUrl('login') . '">' . $signInText . '</a>';
                $bodyText = str_replace('{{ sign_in }}', $signIn, $bodyText);

                return $this->render('AppBundle:User:registration-thankyou.html.twig', [
                    'bodyText' => $bodyText,
                    'email'    => $email,
                ]);
            } catch (\Exception $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->get('first')->addError(new FormError($translator->trans('email.first.existingError', [], 'register')));
                        break;

                    case 421:
                        $form->addError(new FormError($translator->trans('formErrors.matching', [], 'register')));
                        break;

                    case 424:
                        $form->get('postcode')->addError(new FormError($translator->trans('postcode.matchingError', [], 'register')));
                        break;

                    case 425:
                        $form->addError(new FormError($translator->trans('formErrors.caseNumberAlreadyUsed', [], 'register')));
                        break;

                    default:
                        $form->addError(new FormError($translator->trans('formErrors.generic', [], 'register')));
                }

                $this->get('logger')->error(__METHOD__ . ': ' . $e->getMessage() . ', code: ' . $e->getCode());
            }
        }

        // send different URL to google analytics
        if (count($form->getErrors())) {
            $vars['gaCustomUrl'] = '/register/form-errors';
        }

        return $vars + [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user/agree-terms-use/{token}", name="user_agree_terms_use")
     * @Template()
     */
    public function agreeTermsUseAction(Request $request, $token)
    {
        $user = $this->getRestClient()->loadUserByToken($token);

        $form = $this->createForm(new FormDir\User\AgreeTermsType(), $user);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getRestClient()->agreeTermsUse($token);

            return $this->redirectToRoute('user_activate', ['token' => $token, 'action' => 'activate']);
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
        ];
    }

    /**
     * @param EntityDir\User $user
     *
     * @return array [FormType, array of JMS groups]
     */
    private function getFormAndJmsGroupBasedOnUserRole(EntityDir\User $user)
    {
        // define form, route, JMS groups
        switch ($user->getRoleName()) {
            case EntityDir\User::ROLE_ADMIN:
            case EntityDir\User::ROLE_AD:
                return [new FormDir\User\UserDetailsBasicType(), ['user_details_basic']];

            case EntityDir\User::ROLE_LAY_DEPUTY:
                return [new FormDir\User\UserDetailsFullType(), ['user_details_full']];

            case EntityDir\User::ROLE_PA:
            case EntityDir\User::ROLE_PA_ADMIN:
            case EntityDir\User::ROLE_PA_TEAM_MEMBER:
                return [new FormDir\User\UserDetailsPaType(), ['user_details_pa']];
        }
    }
}
