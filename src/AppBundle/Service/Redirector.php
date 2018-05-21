<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Redirector
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var string
     */
    protected $env;

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
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authChecker
     * @param RouterInterface               $router
     * @param Session                       $session
     * @param $env
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        RouterInterface $router,
        Session $session,
        $env
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->router = $router;
        $this->session = $session;
        $this->env = $env;
    }

    /**
     * @return \AppBundle\Entity\User
     */
    private function getLoggedUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * @return string
     */
    public function getFirstPageAfterLogin()
    {
        $user = $this->getLoggedUser();

        if ($this->authChecker->isGranted(EntityDir\User::ROLE_ADMIN)) {
            return $this->router->generate('admin_homepage');
        } if ($this->authChecker->isGranted(EntityDir\User::ROLE_CASE_MANAGER)) {
            return $this->router->generate('admin_client_search');
        } elseif ($this->authChecker->isGranted(EntityDir\User::ROLE_AD)) {
            return $this->router->generate('ad_homepage');
        } elseif ($user->isDeputyOrg()) {
            return $this->router->generate('org_dashboard');
        } elseif ($this->authChecker->isGranted(EntityDir\User::ROLE_LAY_DEPUTY)) {
            return $this->getLayDeputyHomepage($user, false);
        } else {
            return $this->router->generate('access_denied');
        }
    }

    /**
     * //TODO refactor remove. seeem overcomplicated
     * @param  EntityDir\User $user
     * @param  string         $currentRoute
     * @return bool|string
     */
    public function getCorrectRouteIfDifferent(EntityDir\User $user, $currentRoute)
    {
        // Redirect to appropriate homepage
        if (in_array($currentRoute, ['lay_home','ndr_index'])) {
            $route = $user->isNdrEnabled() ? 'ndr_index' : 'lay_home';
        }

        //none of these corrections apply to admin
        if (!in_array($user->getRoleName(), [EntityDir\User::ROLE_ADMIN, EntityDir\User::ROLE_CASE_MANAGER])) {
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
     * @return string
     */
    private function getLayDeputyHomepage(EntityDir\User $user, $enabledLastAccessedUrl = false)
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

        return $this->router->generate('lay_home');
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
        if ($this->env === 'admin') {
            // admin domain: redirect to specific admin/ad homepage, or login page (if not logged)
            if ($this->authChecker->isGranted(EntityDir\User::ROLE_ADMIN)) {
                return $this->router->generate('admin_homepage');
            }
            if ($this->authChecker->isGranted(EntityDir\User::ROLE_CASE_MANAGER)) {
                return $this->router->generate('admin_client_search');
            }
            if ($this->authChecker->isGranted(EntityDir\User::ROLE_AD)) {
                return $this->router->generate('ad_homepage');
            }

            return $this->router->generate('login');
        }

        if ($this->authChecker->isGranted(EntityDir\User::ROLE_PA)) {
            return $this->router->generate('org_dashboard');
        }

        // deputy: if logged, redirect to overview pages
        if ($this->authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->getLayDeputyHomepage($this->getLoggedUser(), false);
        }

        return false;
    }
}
