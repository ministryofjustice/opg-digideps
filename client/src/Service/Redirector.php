<?php

namespace App\Service;

use App\Entity as EntityDir;
use App\Entity\User;
use Nyholm\Psr7\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * Redirect constructor
     *
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     * @param string $env
     */
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected AuthorizationCheckerInterface $authChecker,
        protected RouterInterface $router,
        protected RequestStack $requestStack,
        protected string $env
    ) {
    }

    /**
     * @return UserInterface
     */
    private function getLoggedUser(): UserInterface
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * @param SessionInterface $session
     * @return bool|string
     */
    public function getFirstPageAfterLogin(SessionInterface $session): bool|string
    {
        $user = $this->getLoggedUser();

        if ($this->authChecker->isGranted(EntityDir\User::ROLE_ADMIN)) {
            return $this->router->generate('admin_homepage');
        } elseif ($this->authChecker->isGranted(EntityDir\User::ROLE_AD)) {
            return $this->router->generate('ad_homepage');
        } elseif ($user->isDeputyOrg()) {
            if ($session->has('login-context') && 'password-create' === $session->get('login-context')) {
                return $this->router->generate('user_details');
            } else {
                return $this->router->generate('org_dashboard');
            }
        } elseif ($this->authChecker->isGranted(EntityDir\User::ROLE_LAY_DEPUTY)) {
            return $this->getLayDeputyHomepage($user);
        } else {
            return $this->router->generate('access_denied');
        }
    }

    /**
     * @param User $user
     * @param string $currentRoute
     * @return bool|string
     *
     * TODO: Refactor -- seems overcomplicated
     */
    public function getCorrectRouteIfDifferent(User $user, string $currentRoute): bool|string
    {
        // Redirect to appropriate homepage
        if (in_array($currentRoute, ['lay_home', 'ndr_index'])) {
            $route = $user->isNdrEnabled() ? 'ndr_index' : 'lay_home';
        }

        //none of these corrections apply to admin
        if (!$user->hasAdminRole()) {
            if ($user->getIsCoDeputy()) {
                // already verified - shouldn't be on verification page
                if ('codep_verification' == $currentRoute && $user->getCoDeputyClientConfirmed()) {
                    $route = $user->isNdrEnabled() ? 'ndr_index' : 'lay_home';
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
     * @param UserInterface $user
     * @return bool|string
     */
    private function getLayDeputyHomepage(UserInterface $user): bool|string
    {
        // checks if user has missing details or is NDR
        if ($route = $this->getCorrectRouteIfDifferent($user, 'lay_home')) {
            return $this->router->generate($route);
        }

        // last accessed url
        $lastUrl = $this->getLastAccessedUrl();
        if (!$lastUrl) {
            return $lastUsedUri;
        }

        // redirect to create report if report is not created
        if (0 == $user->getNumberOfReports()) {
            return $this->router->generate('report_create', ['clientId' => $user->getIdOfClientWithDetails()]);
        }

        return $this->router->generate('lay_home');
    }

    /**
     * @return bool|string
     */
    private function getLastAccessedUrl(): bool|string
    {
        $lastUsedUrl = $this->requestStack->getSession()->get('_security.secured_area.target_path');
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

    public function removeLastAccessedUrl(): void
    {
        $this->requestStack->getSession()->remove('_security.secured_area.target_path');
    }

    /**
     * @return bool|string
     */
    public function getHomepageRedirect(): bool|string
    {
        if ('admin' === $this->env) {
            // admin domain: redirect to specific admin/ad homepage, or login page (if not logged)
            if ($this->authChecker->isGranted(EntityDir\User::ROLE_ADMIN)) {
                return $this->router->generate('admin_homepage');
            }
            if ($this->authChecker->isGranted(EntityDir\User::ROLE_AD)) {
                return $this->router->generate('ad_homepage');
            }

            return $this->router->generate('login');
        }

        // PROF and PA redirect to org homepage
        if ($this->authChecker->isGranted(EntityDir\User::ROLE_ORG)) {
            return $this->router->generate('org_dashboard');
        }

        // deputy: if logged, redirect to overview pages
        if ($this->authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->getLayDeputyHomepage($this->getLoggedUser());
        }

        return false;
    }
}
