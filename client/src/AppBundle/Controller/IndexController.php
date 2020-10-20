<?php

namespace AppBundle\Controller;

use AppBundle\Form as FormDir;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\DeputyProvider;
use AppBundle\Service\Redirector;
use AppBundle\Service\StringUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;

class IndexController extends AbstractController
{
    /** @var DeputyProvider */
    private $deputyProvider;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var TranslatorInterface */
    private $translator;

    /** @var string  */
    private $environment;

    /** @var RestClient */
    private $restClient;

    /** @var Router  */
    private $router;

    public function __construct(
        RestClient $restClient,
        DeputyProvider $deputyProvider,
        EventDispatcherInterface $eventDispatcher,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        Router $router,
        string $environment
    )
    {
        $this->deputyProvider = $deputyProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->environment = $environment;
        $this->restClient = $restClient;
        $this->router = $router;
    }

    /**
     * @Route("/", name="homepage")
     *
     * @param Redirector $redirector
     * @return RedirectResponse|Response|null
     */
    public function indexAction(Redirector $redirector)
    {
        if ($url = $redirector->getHomepageRedirect()) {
            return $this->redirect($url);
        }

        // deputy homepage with links to register and login
        return $this->render('AppBundle:Index:index.html.twig', [
                'environment' => $this->environment
            ]);
    }

    /**
     * @Route("login", name="login")
     * @Template("AppBundle:Index:login.html.twig")
     *
     * @param Request $request
     * @return Response|null
     */
    public function loginAction(Request $request)
    {
        $form = $this->createForm(FormDir\LoginType::class, null, [
            'action' => $this->generateUrl('login'),
        ]);
        $form->handleRequest($request);
        $vars = [
            'isAdmin' => $this->container->getParameter('env') === 'admin',
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->logUserIn($form->getData(), $request, [
                    '_adId' => null,
                    '_adFirstname' =>  null,
                    '_adLastname' => null,
                    'loggedOutFrom' => null,
                ]);
            } catch (\Throwable $e) {
                $error = $e->getMessage();

                if ($e->getCode() == 423 && method_exists($e, 'getData')) {
                    $lockedFor = ceil(($e->getData()['data'] - time()) / 60);
                    $error = $this->translator->trans('bruteForceLocked', ['%minutes%' => $lockedFor], 'signin');
                }

                if ($e->getCode() == 499) {
                    // too-many-attempts warning. captcha ?
                }

                $form->addError(new FormError($error));

                return $this->render('AppBundle:Index:login.html.twig', [
                        'form' => $form->createView(),
                    ] + $vars);
            }
        }

        // different page version for timeout and manual logout
        /** @var SessionInterface */
        $session = $request->getSession();

        if ($session->get('loggedOutFrom') === 'logoutPage') {
            $session->set('loggedOutFrom', null); //avoid display the message at next page reload
            return $this->render('AppBundle:Index:login-from-logout.html.twig', [
                    'form' => $form->createView()
                ] + $vars);
        } elseif ($session->get('loggedOutFrom') === 'timeout' || $request->query->get('from') === 'api') {
            $session->set('loggedOutFrom', null); //avoid display the message at next page reload
            $vars['error'] = $this->translator->trans('sessionTimeoutOutWarning', [
                '%time%' => StringUtils::secondsToHoursMinutes($this->container->getParameter('session_expire_seconds')),
            ], 'signin');
        }

        $snSetting = $this->restClient->get('setting/service-notification', 'Setting', [], ['addAuthToken'=>false]);

        return $this->render('AppBundle:Index:login.html.twig', [
                'form' => $form->createView(),
                'serviceNotificationContent' => $snSetting->isEnabled() ? $snSetting->getContent() : null

        ] + $vars);
    }

    /**
     * @Route("login-ad/{userToken}/{adId}/{adFirstname}/{adLastname}", name="ad_login")
     *
     * @param Request $request
     * @param $userToken
     * @param $adId
     * @param $adFirstname
     * @param $adLastname
     *
     * @return Response
     * @throws \Throwable
     */
    public function adLoginAction(Request $request, $userToken, $adId, $adFirstname, $adLastname)
    {
        // logout first
//        $this->get('security.token_storage')->setToken(null);
//        $request->getSession()->invalidate();

        $this->logUserIn(['token' => $userToken], $request, [
            '_adId' => $adId,
            '_adFirstname' =>  $adFirstname,
            '_adLastname' => $adLastname,
            'loggedOutFrom' => null,
        ]);

//        if failing on feature branch, just render a page that does a JS redirect.
//        behat should open the page later and test you don't get redirected

        $url = $this->generateUrl('user_details');

        return new Response("<a href='$url'>continue</a>");
    }

    /**
     * @param array $credentials see RestClient::login()
     * @param Request $request
     * @param array $sessionVars
     *
     * @throws \Throwable
     */
    private function logUserIn($credentials, Request $request, array $sessionVars)
    {
        $user = $this->deputyProvider->login($credentials);
        // manually set session token into security context (manual login)
        $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
        $this->tokenStorage->setToken($token);

        /** @var SessionInterface */
        $session = $request->getSession();
        $session->set('_security_secured_area', serialize($token));
        foreach ($sessionVars as $k=>$v) {
            $session->set($k, $v);
        }

        // regenerate cookie, otherwise gc_* timeouts might logout out after successful login
        $session->migrate();

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch('security.interactive_login', $event);

        $session->set('lastLoggedIn', $user->getLastLoggedIn());
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
     * @param Request $request
     *
     * @return Response|null
     */
    public function error503(Request $request)
    {
        $vars = [];
        $vars['request'] = $request;

        return $this->render('AppBundle:Index:error-503.html.twig', $vars);
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
    public function accessDeniedAction()
    {
        throw new AccessDeniedException();
    }

    /**
     * @Route("/terms", name="terms")
     */
    public function termsAction(Request $request)
    {
        return $this->render('AppBundle:Index:terms.html.twig', [
            'backlink' => $this->getRefererUrlSafe($request, ['terms'])
        ]);
    }

    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacyAction(Request $request)
    {
        return $this->render('AppBundle:Index:privacy.html.twig', [
            'backlink' => $this->getRefererUrlSafe($request, ['privacy'])
        ]);
    }

    /**
     * @Route("/accessibility", name="accessibility")
     */
    public function accessibilityAction(Request $request)
    {
        return $this->render('AppBundle:Index:accessibility.html.twig', [
            'backlink' => $this->getRefererUrlSafe($request, ['accessibility'])
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request)
    {
        $this->tokenStorage->setToken(null);

        /** @var SessionInterface */
        $session = $request->getSession();
        $session->invalidate();

        return $this->redirect(
            $this->generateUrl('homepage')
        );
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
        } elseif ($request->query->get('accept') === 'all') {
            $form->get('usage')->setData(true);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() || $request->query->get('accept') === 'all') {
            $settings = [
                'essential' => true,
                'usage' => $form->get('usage')->getData()
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

        return $this->render('AppBundle:Index:cookies.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Get referer, only if matching an existing route
     *
     * @param  Request $request
     * @param  array   $excludedRoutes
     * @return string|null  referer URL, null if not existing or inside the $excludedRoutes
     */
    protected function getRefererUrlSafe(Request $request, array $excludedRoutes = [])
    {
        $referer = $request->headers->get('referer');

        if (!is_string($referer)) return null;

        $refererUrlPath = parse_url($referer, \PHP_URL_PATH);

        if (!$refererUrlPath) return null;

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
