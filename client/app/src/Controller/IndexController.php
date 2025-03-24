<?php

namespace App\Controller;

use App\Form as FormDir;
use App\Service\Client\RestClient;
use App\Service\DeputyProvider;
use App\Service\Redirector;
use App\Service\StringUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class IndexController extends AbstractController
{
    public function __construct(
        private RestClient $restClient,
        private DeputyProvider $deputyProvider,
        private EventDispatcherInterface $eventDispatcher,
        private TokenStorageInterface $tokenStorage,
        private TranslatorInterface $translator,
        private RouterInterface $router,
        private string $environment,
        private ParameterBagInterface $params,
    ) {
    }

    /**
     * @Route("/", name="homepage")
     *
     * @return RedirectResponse|Response|null
     */
    public function indexAction(Redirector $redirector)
    {
        if ($url = $redirector->getHomepageRedirect()) {
            return $this->redirect($url);
        }

        // deputy homepage with links to register and login
        return $this->render('@App/Index/index.html.twig', [
            'environment' => $this->environment,
        ]);
    }

    /**
     * Session logic for login is now in LoginFormAuthenticator as of Symfony 5.4.
     *
     * @Route("login", name="login")
     *
     * @Template("@App/Index/login.html.twig")
     *
     * @return Response|null
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $form = $this->createForm(FormDir\LoginType::class);
        $vars = [
            'isAdmin' => 'admin' === $this->environment,
        ];

        // See LoginFormAuthenticator - exceptions are set in request session and accessed here once redirected
        $lastAuthError = $authenticationUtils->getLastAuthenticationError();

        if ($lastAuthError) {
            $errorMessage = $lastAuthError->getMessageKey();

            if ($lastAuthError instanceof BadCredentialsException) {
                $errorMessage = $this->translator->trans('signInForm.signin.invalidMessage', [], 'signin');
            }

            if ($lastAuthError instanceof TooManyLoginAttemptsAuthenticationException) {
                $lockedFor = $lastAuthError->getMessageData()['%minutes%'];
                $errorMessage = $this->translator->trans('bruteForceLocked', ['%minutes%' => $lockedFor], 'signin');
            }

            if ($lastAuthError instanceof InvalidCsrfTokenException) {
                $errorMessage = $this->translator->trans('loginInAttemptAfterSessionExpiration', [], 'signin');
            }

            $form->addError(new FormError($errorMessage));

            return $this->render(
                '@App/Index/login.html.twig',
                ['form' => $form->createView()] + $vars
            );
        }

        // different page version for timeout and manual logout
        /** @var SessionInterface */
        $session = $request->getSession();

        if ('logoutPage' === $session->get('loggedOutFrom')) {
            $session->set('loggedOutFrom', null); // avoid display the message at next page reload

            return $this->render('@App/Index/login-from-logout.html.twig', [
                'form' => $form->createView(),
            ] + $vars);
        } elseif ('timeout' === $session->get('loggedOutFrom') || 'api' === $request->query->get('from')) {
            $session->set('loggedOutFrom', null); // avoid display the message at next page reload
            $vars['error'] = $this->translator->trans('sessionTimeoutOutWarning', [
                '%time%' => StringUtils::secondsToHoursMinutes($this->params->get('session_expire_seconds')),
            ], 'signin');
        }

        $snSetting = $this->restClient->get('setting/service-notification', 'Setting', [], ['addAuthToken' => false]);

        return $this->render('@App/Index/login.html.twig', [
            'form' => $form->createView(),
            'serviceNotificationContent' => $snSetting->isEnabled() ? $snSetting->getContent() : null,
            'notPrimaryAccount' => $request->query->get('notPrimaryAccount'),
        ] + $vars);
    }

    /**
     * @Route("login_check", name="login_check")
     */
    public function loginCheckAction()
    {
    }

    /**
     * @Route("error-503", name="error-503")
     *
     * @return Response|null
     */
    public function error503(Request $request)
    {
        $vars = [];
        $vars['request'] = $request;

        return $this->render('@App/Index/error-503.html.twig', $vars);
    }

    /**
     * keep session alive. Called from session timeout dialog.
     *
     * @Route("session-keep-alive", name="session-keep-alive", methods={"GET"})
     */
    public function sessionKeepAliveAction(Request $request)
    {
        /** @var SessionInterface */
        $session = $request->getSession();
        $session->set('refreshedAt', time());

        return new Response('session refreshed successfully');
    }

    /**
     * @Route("/access-denied", name="access_denied")
     */
    public function accessDenied()
    {
        return new Response(
            $this->renderView('@App/Index/access-denied.html.twig'),
            403  // return code
        );
    }

    /**
     * @Route("/terms", name="terms")
     */
    public function termsAction(Request $request)
    {
        return $this->render('@App/Index/terms.html.twig', [
            'backlink' => $this->getRefererUrlSafe($request, ['terms']),
        ]);
    }

    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacyAction(Request $request)
    {
        return $this->render('@App/Index/privacy.html.twig', [
            'backlink' => $this->getRefererUrlSafe($request, ['privacy']),
        ]);
    }

    /**
     * @Route("/accessibility", name="accessibility")
     */
    public function accessibilityAction(Request $request)
    {
        return $this->render('@App/Index/accessibility.html.twig', [
            'backlink' => $this->getRefererUrlSafe($request, ['accessibility']),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logoutAction(Request $request)
    {
        // Handled as automatically as part of Symfony security component
    }

    /**
     * @Route("/cookies", name="cookies")
     */
    public function cookiesAction(Request $request)
    {
        $form = $this->createForm(FormDir\CookiePermissionsType::class);

        if ($request->cookies->has('cookie_policy')) {
            $policy = json_decode($request->cookies->get('cookie_policy'));
            $form->get('usage')->setData($policy->usage);
        } elseif ('all' === $request->query->get('accept')) {
            $form->get('usage')->setData(true);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() || 'all' === $request->query->get('accept')) {
            $settings = [
                'essential' => true,
                'usage' => $form->get('usage')->getData(),
            ];
            setcookie(
                'cookie_policy',
                strval(json_encode($settings)),
                time() + (60 * 60 * 24 * 365),
                '',
                '',
                true
            );
        }

        return $this->render('@App/Index/cookies.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * There is an issue with a user's data which has to be manually corrected; for example, a missing deputy UID
     * or a client without the necessary address data.
     *
     * @Route("/invalid-data", name="invalid_data_action")
     */
    public function invalidDataAction(): Response
    {
        return $this->renderError(description: 'Data error', statusCode: 422, message: 'INVALID DATA');
    }

    /**
     * Get referer, only if matching an existing route.
     *
     * @return string|null referer URL, null if not existing or inside the $excludedRoutes
     */
    protected function getRefererUrlSafe(Request $request, array $excludedRoutes = [])
    {
        $referer = $request->headers->get('referer');

        if (!is_string($referer)) {
            return null;
        }

        $refererUrlPath = parse_url($referer, \PHP_URL_PATH);

        if (!$refererUrlPath) {
            return null;
        }

        try {
            $routeParams = $this->router->match($refererUrlPath);
        } catch (ResourceNotFoundException $e) {
            return null;
        }
        $routeName = $routeParams['_route'];
        if (in_array($routeName, $excludedRoutes)) {
            return null;
        }
        unset($routeParams['_route']);

        return $this->router->generate($routeName, $routeParams);
    }
}
