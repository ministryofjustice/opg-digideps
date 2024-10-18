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
        private ClientApi $clientApi,
        private ParameterStoreService $parameterStoreService
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
            return $this->getCorrectLayHomepage();
        } else {
            return $this->router->generate('access_denied');
        }
    }

    /**
     * //TODO refactor remove. seeem overcomplicated.
     *
     * @param string $currentRoute
     *
     * @return bool|string
     */
    public function getCorrectRouteIfDifferent(User $user, $currentRoute)
    {
        // Check if user has multiple clients
        $clients = !is_null($user->getDeputyUid()) ? $this->clientApi->getAllClientsByDeputyUid($user->getDeputyUid()) : null;
        $multiClientDeputy = !is_null($clients) && count($clients) > 1;

        // Redirect to appropriate homepage
        if (in_array($currentRoute, ['lay_home', 'ndr_index'])) {
            if ($multiClientDeputy) {
                $route = 'lay_home';
            } else {
                $route = $user->isNdrEnabled() ? 'ndr_index' : 'lay_home';
            }
        }

        // none of these corrections apply to admin
        if (!$user->hasAdminRole()) {
            if ($user->getIsCoDeputy()) {
                // already verified - shouldn't be on verification page
                if ('codep_verification' == $currentRoute && $user->getCoDeputyClientConfirmed()) {
                    if ($multiClientDeputy) {
                        $route = 'lay_home';
                    } else {
                        $route = $user->isNdrEnabled() ? 'ndr_index' : 'lay_home';
                    }
                }

                // unverified codeputy invitation
                if (!$user->getCoDeputyClientConfirmed()) {
                    $route = 'codep_verification';
                }
            } else {
                if (!$user->isDeputyOrg()) {
                    // client is not added
                    if (!$user->getIdOfClientWithDetails()) {
                        $route = 'client_add';
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
    private function getLayDeputyHomepage(User $user, $enabledLastAccessedUrl = false)
    {
        // checks if user has missing details or is NDR
        if ($route = $this->getCorrectRouteIfDifferent($user, 'lay_home')) {
            return $this->router->generate($route);
        }

        // last accessed url
        if ($enabledLastAccessedUrl && $lastUsedUri = $this->getLastAccessedUrl()) {
            return $lastUsedUri;
        }

        // redirect to create report if report is not created
        if (0 == $user->getNumberOfReports()) {
            return $this->router->generate('report_create', ['clientId' => $user->getIdOfClientWithDetails()]);
        }

        return $this->router->generate('lay_home', ['clientId' => $user->getIdOfClientWithDetails()]);
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
        $isMultiClientFeatureEnabled = $this->parameterStoreService->getFeatureFlag(ParameterStoreService::FLAG_MULTI_ACCOUNTS);
        $user = $this->getLoggedUser();

        $clients = !is_null($user->getDeputyUid()) ? $this->clientApi->getAllClientsByDeputyUid($user->getDeputyUid()) : null;

        if ('1' == $isMultiClientFeatureEnabled) {
            if (!(null === $clients)) {
                if (1 < count($clients)) {
                    return $this->getChooseAClientHomepage($user);
                } else {
                    return $this->getLayDeputyHomepage($user);
                }
            } else {
                return $this->getLayDeputyHomepage($user);
            }
        } else {
            return $this->getLayDeputyHomepage($user);
        }
    }
}
