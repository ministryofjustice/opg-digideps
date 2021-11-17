<?php

namespace App\Controller;

use App\Entity as EntityDir;
use App\Exception\RestClientException;
use App\Form as FormDir;
use App\Model\SelfRegisterData;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\DeputyProvider;
use App\Service\Redirector;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    private RestClient $restClient;
    private UserApi $userApi;
    private ClientApi $clientApi;
    private TranslatorInterface $translator;
    private LoggerInterface $logger;
    private DateTimeProvider $dateTimeProvider;

    public function __construct(
        RestClient $restClient,
        UserApi $userApi,
        ClientApi $clientApi,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        DateTimeProvider $dateTimeProvider
    ) {
        $this->restClient = $restClient;
        $this->userApi = $userApi;
        $this->clientApi = $clientApi;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * Landing page to let the user access the app and selecting a password.
     *
     * Used for both user activation (Step1) or password reset. The controller logic is very similar
     *
     * @Route("/user/{action}/{token}", name="user_activate", defaults={ "action" = "activate"}, requirements={
     *   "action" = "(activate|password-reset)"
     * })
     */
    public function activateUserAction(
        Request $request,
        Redirector $redirector,
        DeputyProvider $deputyProvider,
        string $action,
        string $token,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ): Response {
        $isActivatePage = 'activate' === $action;

        // check $token is correct
        try {
            /* @var $user EntityDir\User */
            $user = $this->restClient->loadUserByToken($token);
        } catch (\Throwable $e) {
            return $this->renderError('This link is not working or has already been used', $e->getCode());
        }

        // token expired
        if (!$user->isTokenSentInTheLastHours(EntityDir\User::TOKEN_EXPIRE_HOURS)) {
            $template = $isActivatePage ? '@App/User/activateTokenExpired.html.twig' : '@App/User/passwordResetTokenExpired.html.twig';

            return $this->render($template, [
                'token' => $token,
                'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
            ]);
        }

        // PA must agree to terms before activating the account
        // this check happens before activating the account, therefore no need to set an ACL on all the actions
        if (
            $isActivatePage
            && $user->hasRoleOrgNamed()
            && !$user->getAgreeTermsUse()
        ) {
            return $this->redirectToRoute('user_agree_terms_use', ['token' => $token]);
        }

        // define form and template that differs depending on the action (activate or password-reset)
        if ($isActivatePage) {
            $passwordMismatchMessage = $this->translator->trans('password.validation.passwordMismatch', [], 'user-activate');
            $form = $this->createForm(
                FormDir\SetPasswordType::class,
                $user,
                ['passwordMismatchMessage' => $passwordMismatchMessage, 'showTermsAndConditions' => $user->isDeputy()]
            );
            $template = '@App/User/activate.html.twig';
        } else { // 'password-reset'
            $passwordMismatchMessage = $this->translator->trans('form.password.validation.passwordMismatch', [], 'password-reset');
            $form = $this->createForm(FormDir\ResetPasswordType::class, $user, ['passwordMismatchMessage' => $passwordMismatchMessage]);
            $template = '@App/User/passwordReset.html.twig';
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // login user into API
            try {
                $deputyProvider->login(['token' => $token]);
            } catch (UsernameNotFoundException $e) {
                return $this->renderError('This activation link is not working or has already been used');
            }

            /** @var string */
            $data = json_encode([
                'password_plain' => $user->getPassword(),
                'set_active' => true,
            ]);

            // set password for user
            $this->restClient->put('user/'.$user->getId().'/set-password', $data);

            // set agree terms for user
            $this->userApi->agreeTermsUse($token);

            // log in
            $clientToken = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $tokenStorage->setToken($clientToken); //now the user is logged in

            $session->set('_security_secured_area', serialize($clientToken));

            if ($isActivatePage) {
                $request->getSession()->set('login-context', 'password-create');
                $route = $user->getIsCoDeputy() ? 'codep_verification' : 'user_details';

                return $this->redirectToRoute($route);
            } else {
                $request->getSession()->set('login-context', 'password-update');

                return $this->redirect($redirector->getFirstPageAfterLogin($request->getSession()));
            }
        }

        return $this->render($template, [
            'token' => $token,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/user/activate/password/send/{token}", name="activation_link_send")
     * @Template("@App/User/activateLinkSend.html.twig")
     */
    public function activateLinkSendAction(string $token): Response
    {
        $user = $this->restClient->loadUserByToken($token);
        $this->userApi->activate($user->getEmail());

        return $this->redirect($this->generateUrl('activation_link_sent', ['token' => $token]));
    }

    /**
     * @return array<mixed>
     * @Route("/user/activate/password/sent/{token}", name="activation_link_sent")
     * @Template("@App/User/activateLinkSent.html.twig")
     */
    public function activateLinkSentAction(string $token): array
    {
        return [
            'token' => $token,
            'tokenExpireHours' => EntityDir\User::TOKEN_EXPIRE_HOURS,
        ];
    }

    /**
     * Page to edit user details.
     * For :
     * - admin
     * - AD
     * - Lay
     * - PA.
     *
     * @return array<mixed>|Response
     * @Route("/user/details", name="user_details")
     * @Template("@App/User/details.html.twig")
     */
    public function detailsAction(Request $request, Redirector $redirector)
    {
        $user = $this->userApi->getUserWithData();

        $client_validated = $this->clientApi->getFirstClient() instanceof EntityDir\Client &&
            !$user->isDeputyOrg();

        list($formType, $jmsPutGroups) = $this->getFormAndJmsGroupBasedOnUserRole($user);
        $form = $this->createForm($formType, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('user/'.$user->getId(), $form->getData(), $jmsPutGroups);

            // lay deputies are redirected to adding a client (Step.3)
            if ($user->isLayDeputy()) {
                return $this->redirectToRoute('client_add');
            }

            // all other users go to their homepage (dashboard for PROF/PA), or /admin for Admins
            return $this->redirect($redirector->getHomepageRedirect());
        }

        return [
            'client_validated' => $client_validated,
            'form' => $form->createView(),
            'user' => $user,
        ];
    }

    /**
     * @return array<mixed>|Response
     * @Route("/password-managing/forgotten", name="password_forgotten")
     * @Template("@App/User/passwordForgotten.html.twig")
     **/
    public function passwordForgottenAction(Request $request)
    {
        $user = new EntityDir\User();
        $form = $this->createForm(FormDir\PasswordForgottenType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userApi->resetPassword($user->getEmail());
            } catch (RestClientException $e) {
                $this->logger->warning('Email '.$user->getEmail().' not found');
            }

            // after details are added, admin users to go their homepage, deputies go to next step
            return $this->redirect($this->generateUrl('password_sent'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @return array<mixed>
     * @Route("/password-managing/sent", name="password_sent")
     * @Template("@App/User/passwordSent.html.twig")
     */
    public function passwordSentAction(): array
    {
        return [];
    }

    /**
     * @return array<mixed>|Response
     * @Route("/register", name="register")
     * @Template("@App/User/register.html.twig")
     */
    public function registerAction(Request $request)
    {
        $selfRegisterData = new SelfRegisterData();
        $form = $this->createForm(FormDir\SelfRegisterDataType::class, $selfRegisterData);

        $vars = [];

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $this->userApi->selfRegister($data);

                $bodyText = $this->translator->trans('thankyou.body', [], 'register');
                $email = $data->getEmail();
                $bodyText = str_replace('{{ email }}', $email, $bodyText);

                $signInText = $this->translator->trans('signin', [], 'register');
                $signIn = '<a href="'.$this->generateUrl('login').'">'.$signInText.'</a>';
                $bodyText = str_replace('{{ sign_in }}', $signIn, $bodyText);

                return $this->render('@App/User/registration-thankyou.html.twig', [
                    'bodyText' => $bodyText,
                    'email' => $email,
                ]);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 403:
                        $form->addError(new FormError($this->translator->trans('formErrors.coDepCaseAlreadyRegistered', [], 'register')));
                        break;

                    case 422:
                        $form->addError(new FormError(
                            $this->translator->trans('email.first.existingError', [], 'register')
                        ));
                        break;

                    case 400:
                        $form->addError(new FormError($this->translator->trans('formErrors.matching', [], 'register')));
                        break;

                    case 424:
                        $form->get('postcode')->addError(new FormError($this->translator->trans('postcode.matchingError', [], 'register')));
                        break;

                    case 425:
                        $form->addError(new FormError($this->translator->trans('formErrors.caseNumberAlreadyUsed', [], 'register')));
                        break;

                    default:
                        $form->addError(new FormError($this->translator->trans('formErrors.generic', [], 'register')));
                }

                $failureData = json_decode($e->getData()['message'], true);

                // If response from API is not valid json just log the message
                $failureData = !is_array($failureData) ? ['failure_message' => $failureData] : $failureData;

                $this->logger->notice('', (new AuditEvents($this->dateTimeProvider))->selfRegistrationFailed($failureData));
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
     */
    public function agreeTermsUseAction(Request $request, string $token): Response
    {
        $user = $this->restClient->loadUserByToken($token);

        $form = $this->createForm(FormDir\User\AgreeTermsType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userApi->agreeTermsUse($token);

            return $this->redirectToRoute('user_activate', ['token' => $token, 'action' => 'activate']);
        }

        if (EntityDir\User::ROLE_PA_NAMED == $user->getRoleName()) {
            $view = '@App/User/agreeTermsUsePa.html.twig';
        } elseif (EntityDir\User::ROLE_PROF_NAMED == $user->getRoleName()) {
            $view = '@App/User/agreeTermsUseProf.html.twig';
        } else {
            throw new \RuntimeException('terms page not implemented');
        }

        return $this->render($view, [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/update-terms-use/{token}", name="user_updated_terms_use")
     * @Security("is_granted('ROLE_ORG')")
     */
    public function updatedTermsUseAction(Request $request, string $token): Response
    {
        $user = $this->restClient->loadUserByToken($token);

        $form = $this->createForm(FormDir\User\UpdateTermsType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userApi->agreeTermsUse($token);

            return $this->redirectToRoute('org_dashboard');
        }

        return $this->render('@App/User/updatedTermsUse.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return array<mixed> [string FormType, array of JMS groups]
     */
    private function getFormAndJmsGroupBasedOnUserRole(EntityDir\User $user): array
    {
        // define form, route, JMS groups
        switch ($user->getRoleName()) {
            case EntityDir\User::ROLE_LAY_DEPUTY:
                return [FormDir\User\UserDetailsFullType::class, ['user_details_full']];

            case EntityDir\User::ROLE_PA_NAMED:
            case EntityDir\User::ROLE_PA_ADMIN:
            case EntityDir\User::ROLE_PA_TEAM_MEMBER:
            case EntityDir\User::ROLE_PROF_NAMED:
            case EntityDir\User::ROLE_PROF_ADMIN:
            case EntityDir\User::ROLE_PROF_TEAM_MEMBER:
                return [FormDir\User\UserDetailsPaType::class, ['user_details_org']];

            case EntityDir\User::ROLE_ADMIN:
            case EntityDir\User::ROLE_AD:
            case EntityDir\User::ROLE_SUPER_ADMIN:
            default:
                return [FormDir\User\UserDetailsBasicType::class, ['user_details_basic']];
        }
    }
}
