<?php

namespace App\Service;

use App\Entity\User;
use App\Service\Client\Internal\ClientApi;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * getFirstPageAfterLogin() called after authentication
 * getHomepageRedirect() called if returning to base domain
 * Both methods have self-contained logic for prof/pa and admin landing pages
 * For lays both will direct to getLayDeputyHomepage(), which calls to getCorrectRouteIfDifferent()
 * This logic is used to determine which page the user should be directed to depending on their status (NDR/Co-Deputy/Multi-client).
 */
class Redirector
{
    /**
     * Routes the user can be redirected to, if accessed before timeout.
     *
     * @var array
     */
    private $redirectableRoutes = [
        'user_details',
        'user_edit',
        'report_overview',
        'account',
        'accounts',
        'contacts',
        'decisions',
        'assets',
        'report_declaration',
        'report_submit_confirmation',
        'client',
    ];

    /**
     * Redirector constructor.
     */
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected AuthorizationCheckerInterface $authChecker,
        protected RouterInterface $router,
        protected Session $session,
        protected string $env,
        private ClientApi $clientApi
    ) {
    }

    /**
     * @return User
     */
    private function getLoggedUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * @return string
     */
    public function getFirstPageAfterLogin(SessionInterface $session)
    {
        $user = $this->getLoggedUser();

        if ($this->authChecker->isGranted(User::ROLE_ADMIN)) {
            if ($session->has('login-context') && 'password-create' === $session->get('login-context')) {
                return $this->router->generate('user_details');
            } else {
                return $this->router->generate('admin_homepage');
            }
        } elseif ($this->authChecker->isGranted(User::ROLE_AD)) {
            return $this->router->generate('ad_homepage');
        } elseif ($user->isDeputyOrg()) {
            if ($session->has('login-context') && 'password-create' === $session->get('login-context')) {
                return $this->router->generate('user_details');
            } else {
                return $this->router->generate('org_dashboard');
            }
        } elseif ($this->authChecker->isGranted(User::ROLE_LAY_DEPUTY)) {
            file_put_contents('php://stderr', print_r('HELLOOOOO1: ', true));

            return $this->getCorrectLayHomepage();
        } else {
            return $this->router->generate('access_denied');
        }
    }

    /**
     * //TODO refactor remove. seem overcomplicated.
     *
     * @param string $currentRoute
     *
     * @return bool|string
     */
    public function getCorrectRouteIfDifferent(User $user, $currentRoute)
    {
        // Check if user has multiple clients
        $clients = !is_null($user->getDeputyUid()) ? $this->clientApi->getAllClientsByDeputyUid($user->getDeputyUid()) : [];
        $multiClientDeputy = !is_null($clients) && count($clients) > 1;

        // Redirect to appropriate homepage
        if (in_array($currentRoute, ['lay_home', 'ndr_index'])) {
            $route = 'lay_home';
        }

        // none of these corrections apply to admin
        if (!$user->hasAdminRole()) {
            if ($user->getIsCoDeputy()) {
                // already verified - shouldn't be on verification page
                if ('codep_verification' == $currentRoute && $user->getCoDeputyClientConfirmed()) {
                    $route = 'lay_home';
                }

                // unverified codeputy invitation
                if (!$user->getCoDeputyClientConfirmed()) {
                    $route = 'codep_verification';
                }
            } else {
                if (!$user->isDeputyOrg()) {
                    // client is not added
                    if (!$user->getIdOfClientWithDetails()) {
                        if (0 == count($clients)) {
                            $route = 'client_add';
                        }
                    }

                    // incomplete user info
                    if (!$user->hasAddressDetails()) {
                        $route = 'user_details';
                    }
                }
            }
        }

        return (!empty($route) && $route !== $currentRoute) ? $route : false;
    }

    /**
     * @return string
     */
    private function getLayDeputyHomepage(User $user, $activeClientId = null, $enabledLastAccessedUrl = false)
    {
        // checks if user has missing details or is NDR
        if ($route = $this->getCorrectRouteIfDifferent($user, 'lay_home')) {
            file_put_contents('php://stderr', print_r('HELLOOOOO4: ', true));

            return $this->router->generate($route);
        }

        file_put_contents('php://stderr', print_r('HELLOOOOO5: ', true));

        // last accessed url
        if ($enabledLastAccessedUrl && $lastUsedUri = $this->getLastAccessedUrl()) {
            return $lastUsedUri;
        }

        // redirect to create report if report is not created
        $allActiveClients = $this->clientApi->getAllClientsByDeputyUid($user->getDeputyUid(), ['client-reports', 'report']);

        foreach ($allActiveClients as $activeClient) {
            if (count($activeClient->getReportIds()) >= 1) {
                break;
            }

            if (!$user->isNdrEnabled()) {
                return $this->router->generate('report_create', ['clientId' => $user->getIdOfClientWithDetails()]);
            }
        }

        // check if last remaining active client is linked to non-primary account if so retrieve id
        return null == $activeClientId ? $this->router->generate('lay_home', ['clientId' => $user->getIdOfClientWithDetails()]) :
            $this->router->generate('lay_home', ['clientId' => $activeClientId]);
    }

    /**
     * @return bool|string
     */
    private function getLastAccessedUrl()
    {
        $lastUsedUrl = $this->session->get('_security.secured_area.target_path');
        if (!$lastUsedUrl) {
            return false;
        }

        $urlPieces = parse_url($lastUsedUrl);
        if (empty($urlPieces['path'])) {
            return false;
        }

        try {
            $route = $this->router->match($urlPieces['path'])['_route'];
        } catch (ResourceNotFoundException $e) {
            return false;
        }

        if (in_array($route, $this->redirectableRoutes)) {
            return $lastUsedUrl;
        }

        return false;
    }

    public function removeLastAccessedUrl()
    {
        $this->session->remove('_security.secured_area.target_path');
    }

    /**
     * @return string
     */
    public function getHomepageRedirect()
    {
        if ('admin' === $this->env) {
            // admin domain: redirect to specific admin/ad homepage, or login page (if not logged)
            if ($this->authChecker->isGranted(User::ROLE_ADMIN)) {
                return $this->router->generate('admin_homepage');
            }
            if ($this->authChecker->isGranted(User::ROLE_AD)) {
                return $this->router->generate('ad_homepage');
            }

            return $this->router->generate('login');
        }

        // PROF and PA redirect to org homepage
        if ($this->authChecker->isGranted(User::ROLE_ORG)) {
            return $this->router->generate('org_dashboard');
        }

        // deputy: if logged, redirect to overview pages
        if ($this->authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->getCorrectLayHomepage();
        }

        return false;
    }

    /**
     * @return string
     */
    private function getChooseAClientHomepage(User $user, $enabledLastAccessedUrl = false)
    {
        // checks if user has missing details or is NDR
        if ($route = $this->getCorrectRouteIfDifferent($user, 'choose_a_client')) {
            return $this->router->generate($route);
        }

        // last accessed url
        if ($enabledLastAccessedUrl && $lastUsedUri = $this->getLastAccessedUrl()) {
            return $lastUsedUri;
        }

        return $this->router->generate('choose_a_client');
    }

    private function getCorrectLayHomepage()
    {
        $user = $this->getLoggedUser();

        $clients = !is_null($user->getDeputyUid()) ? $this->clientApi->getAllClientsByDeputyUid($user->getDeputyUid()) : [];
        $activeClientId = count($clients) > 0 ? array_values($clients)[0]->getId() : null;

        if (!(null === $clients)) {
            if (1 < count($clients)) {
                return $this->getChooseAClientHomepage($user);
            } else {
                file_put_contents('php://stderr', print_r('HELLOOOOO3: ', true));

                return $this->getLayDeputyHomepage($user, $activeClientId);
            }
        } else {
            return $this->getLayDeputyHomepage($user);
        }
    }
}
