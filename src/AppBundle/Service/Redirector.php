<?php

namespace AppBundle\Service;

use AppBundle\Entity as EntityDir;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class Redirector
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var SecurityContextInterface
     */
    protected $security;

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
     * @param SecurityContextInterface $security
     * @param RouterInterface $router
     * @param Session $session
     * @param $env
     */
    public function __construct(
        SecurityContextInterface $security,
        RouterInterface $router,
        Session $session,
        $env
    ) {
        $this->security = $security;
        $this->router = $router;
        $this->session = $session;
        $this->env = $env;
    }

    /**
     * @return \AppBundle\Entity\User
     */
    private function getLoggedUser()
    {
        return $this->security->getToken()->getUser();
    }

    /**
     * @return string
     */
    public function getFirstPageAfterLogin()
    {
        $user = $this->getLoggedUser();

        if ($this->security->isGranted(EntityDir\User::ROLE_ADMIN)) {
            return $this->router->generate('admin_homepage');
        } elseif ($this->security->isGranted(EntityDir\User::ROLE_AD)) {
            return $this->router->generate('ad_homepage');
        } elseif ($user->isDeputyPa()) {
            return $this->router->generate('pa_dashboard');
        } elseif ($this->security->isGranted(EntityDir\User::ROLE_LAY_DEPUTY)) {
            return $this->getLayDeputyHomepage($user, false);
        } else {
            return $this->router->generate('access_denied');
        }
    }

    /**
     * @param EntityDir\User $user
     * @param string $currentRoute
     * @return bool|string
     */
    public function getCorrectRouteIfDifferent(EntityDir\User $user, $currentRoute)
    {
        // Redirect to appropriate homepage
        if (in_array($currentRoute, ['lay_home','odr_index'])){
            $route = $user->isOdrEnabled() ? 'odr_index' : 'lay_home';
        }

        // client is not added
        if ($user->getIsCoDeputy() && !$user->getCoDeputyClientConfirmed()) {
            $route = 'client_verify';
        } elseif (!$user->getIdOfClientWithDetails()) {
            $route = 'client_add';
        }

        // incomplete user info
        if (!$user->hasDetails()) {
            $route = 'user_details';
        }

        return (!empty($route) && $route !== $currentRoute) ? $route : false;
    }

    /**
     * @return string
     */
    private function getLayDeputyHomepage(EntityDir\User $user, $enabledLastAccessedUrl = false)
    {
        // checks if user has missing details or is ODR
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
        $securityContext = $this->security;

        if ($this->env === 'admin') {
            // admin domain: redirect to specific admin/ad homepage, or login page (if not logged)
            if ($securityContext->isGranted(EntityDir\User::ROLE_ADMIN)
            ) {
                return $this->router->generate('admin_homepage');
            }
            if ($securityContext->isGranted(EntityDir\User::ROLE_AD)) {
                return $this->router->generate('ad_homepage');
            }

            return $this->router->generate('login');
        }

        if ($securityContext->isGranted(EntityDir\User::ROLE_PA)) {
            return $this->router->generate('pa_dashboard');
        }

        // deputy: if logged, redirect to overview pages
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->getLayDeputyHomepage($this->getLoggedUser(), false);
        }

        return false;
    }
}
