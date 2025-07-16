<?php

namespace App\Controller;

use App\Entity as EntityDir;
use App\Event\RegistrationFailedEvent;
use App\Event\RegistrationSucceededEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Exception\RestClientException;
use App\Form as FormDir;
use App\Model\SelfRegisterData;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\DeputyProvider;
use App\Service\Redirector;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    public function __construct(
        private RestClient $restClient,
        private UserApi $userApi,
        private ClientApi $clientApi,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
        private ObservableEventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * Landing page to let the user access the app and selecting a password.
     *
     * Used for both user activation (Step1) or password reset. The controller logic is very similar
     *
     * @Route("/user/{action}/{token}", defaults={"action"="activate"}, requirements={
     *   "action" = "(activate|password-reset)"
     * }, name="user_activate")
     */
    public function activateUserAction(
        Request $request,
        DeputyProvider $deputyProvider,
        string $action,
        string $token,
        RateLimiterFactory $anonymousApiLimiter,
    ): Response {
        $isActivatePage = 'activate' === $action;

        $userId = substr($token, -8);

        // rate limiting applied to track unsuccessful and successful requests
        $limiter = $anonymousApiLimiter->create($userId);
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted() && !$isActivatePage) {
            return $this->renderError(sprintf('You have tried to reset your password too many times. Please try again in %s minutes.', ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60)), 429, 'There is a problem');
        } elseif (!$limit->isAccepted() && $isActivatePage) {
            return $this->renderError(sprintf('You have tried to activate your account too many times. Please try again in %s minutes.', ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60)), 429, 'There is a problem');
        }

        // check $token is correct
        try {
            /* @var $user EntityDir\User */
            $user = $this->restClient->loadUserByToken($token);
        } catch (\Throwable $e) {
            return $this->renderError('This link is not working or has already been used.', $e->getCode(), 'There is a problem');
        }

        // token expired
        if (!$user->isTokenSentInTheLastHours(EntityDir\User::ACTIVATE_TOKEN_EXPIRE_HOURS) && $isActivatePage) {
            $template = '@App/User/activateTokenExpired.html.twig';

            return $this->render($template, [
                'token' => $token,
                'tokenExpireHours' => EntityDir\User::ACTIVATE_TOKEN_EXPIRE_HOURS,
            ]);
        } elseif (!$user->isTokenSentInTheLastHours(EntityDir\User::PASSWORD_TOKEN_EXPIRE_HOURS) && !$isActivatePage) {
            $template = '@App/User/passwordResetTokenExpired.html.twig';

            return $this->render($template, [
                'token' => $token,
                'tokenExpireHours' => EntityDir\User::PASSWORD_TOKEN_EXPIRE_HOURS,
            ]);
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
            } catch (UserNotFoundException $e) {
                return $this->renderError('This activation link is not working or has already been used', 'There is a problem');
            }

            /** @var string */
            $data = json_encode([
                'password' => $user->getPassword(),
                'token' => $token,
            ]);

            // set password for user
            $this->restClient->apiCall('PUT', 'user/'.$user->getId().'/set-password', $data, 'array', [], false);

            if ($user->hasAdminRole()) {
                $this->restClient->apiCall('PUT', 'user/'.$user->getId().'/set-registration-date', null, 'array', [], false);
                $this->restClient->apiCall('PUT', 'user/'.$user->getId().'/set-active', null, 'array', [], false);

                $this->eventDispatcher->dispatch(new RegistrationSucceededEvent($user), RegistrationSucceededEvent::ADMIN);
            }

            // set agree terms for user
            $this->userApi->agreeTermsUse($token);
            $this->userApi->clearRegistrationToken($token);

            if ($isActivatePage) {
                $request->getSession()->set('login-context', 'password-create');

                return $this->redirectToRoute('login');
            } else {
                $request->getSession()->set('login-context', 'password-update');

                return $this->redirectToRoute('login');
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
     *
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
     *
     * @Route("/user/activate/password/sent/{token}", name="activation_link_sent")
     *
     * @Template("@App/User/activateLinkSent.html.twig")
     */
    public function activateLinkSentAction(string $token): array
    {
        return [
            'token' => $token,
            'tokenExpireHours' => EntityDir\User::ACTIVATE_TOKEN_EXPIRE_HOURS,
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
     *
     * @Route("/user/details", name="user_details")
     *
     * @Template("@App/User/details.html.twig")
     */
    public function detailsAction(Request $request, Redirector $redirector)
    {
        $user = $this->userApi->getUserWithData();

        $client_validated = $this->clientApi->getFirstClient() instanceof EntityDir\Client
            && !$user->isDeputyOrg();

        list($formType, $jmsPutGroups) = $this->getFormAndJmsGroupBasedOnUserRole($user);
        $form = $this->createForm($formType, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->put('user/'.$user->getId(), $form->getData(), $jmsPutGroups);

            // lay deputies are redirected to adding a client (Step.3)
            if ($user->isLayDeputy()) {
                return $this->redirectToRoute('client_add');
            }

            //            this is the final step for Org users so registration has succeeded
            if ($user->isDeputyOrg()) {
                $user->setPreRegisterValidatedDate(new \DateTime());
                $this->eventDispatcher->dispatch(new RegistrationSucceededEvent($user), RegistrationSucceededEvent::DEPUTY);
            }
            $request->getSession()->remove('login-context');

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
     *
     * @Route("/password-managing/forgotten", name="password_forgotten")
     *
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
     *
     * @Route("/password-managing/sent", name="password_sent")
     *
     * @Template("@App/User/passwordSent.html.twig")
     */
    public function passwordSentAction(): array
    {
        return [];
    }

    /**
     * @return array<mixed>|Response
     *
     * @Route("/register", name="register")
     *
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

                    case 460:
                        $form->get('caseNumber')->addError(new FormError($this->translator->trans('matchingErrors.caseNumber', [], 'register')));
                        break;

                    case 461:
                        $decodedError = json_decode($e->getData()['message'], true);

                        if (true == $decodedError['matching_errors']['client_lastname']) {
                            $form->get('clientLastname')->addError(new FormError($this->translator->trans('matchingErrors.clientLastname', [], 'register')));
                        }
                        if (true == $decodedError['matching_errors']['deputy_lastname']) {
                            $form->get('lastname')->addError(new FormError($this->translator->trans('matchingErrors.deputyLastname', [], 'register')));
                        }
                        if (true == $decodedError['matching_errors']['deputy_firstname']) {
                            $form->get('firstname')->addError(new FormError($this->translator->trans('matchingErrors.deputyFirstname', [], 'register')));
                        }
                        if (true == $decodedError['matching_errors']['deputy_postcode']) {
                            $form->get('postcode')->addError(new FormError($this->translator->trans('matchingErrors.deputyPostcode', [], 'register')));
                        }

                        break;

                    case 462:
                        $form->addError(new FormError($this->translator->trans('formErrors.deputyNotUniquelyIdentified', [], 'register')));
                        break;

                    default:
                        $form->addError(new FormError($this->translator->trans('formErrors.generic', [], 'register')));
                }

                $failureData = json_decode($e->getData()['message'], true);

                // If response from API is not valid json just log the message
                $failureData = !is_array($failureData) ? ['failure_message' => $failureData] : $failureData;

                $event = new RegistrationFailedEvent($failureData, $e->getMessage());
                $this->eventDispatcher->dispatch($event, RegistrationFailedEvent::NAME);
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
     * @Route("/user/update-terms-use/{token}", name="user_updated_terms_use")
     *
     * @Security("is_granted('ROLE_ORG')")
     */
    public function updatedTermsUseAction(Request $request, string $token): Response
    {
        $user = $this->restClient->loadUserByToken($token);

        $form = $this->createForm(FormDir\User\UpdateTermsType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userApi->agreeTermsUse($token);
            $this->userApi->clearRegistrationToken($token);

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
