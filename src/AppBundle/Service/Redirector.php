<?php

namespace AppBundle\Service;

use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

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
        'report_add_further_info',
        'report_submit_confirmation',
        'client',
    ];

    /**
     * @param \AppBundle\Service\SecurityContext $security
     * @param type                               $router
     */
    public function __construct(
        SecurityContextInterface $security,
        RouterInterface $router,
        Session $session,
        Client\RestClient $restClient,
        $env
    ) {
        $this->security = $security;
        $this->router = $router;
        $this->session = $session;
        $this->restClient = $restClient;
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
    public function getFirstPageAfterLogin($enabledLastAccessedUrl = true)
    {
        $user = $this->getLoggedUser();

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->getAdminHomepage();
        } elseif ($this->security->isGranted('ROLE_LAY_DEPUTY')) {
            return $this->getLayDeputyHomepage($user, $enabledLastAccessedUrl);
        } elseif ($this->security->isGranted('ROLE_AD')) {
            return $this->getAdHomepage($user, $enabledLastAccessedUrl);
        } else {
            return $this->router->generate('access_denied');
        }
    }

    /**
     * @return string URL
     */
    private function getAdminHomepage()
    {
        return $this->router->generate('admin_homepage');
    }

    /**
     * @return string URL
     */
    private function getAdHomepage()
    {
        return $this->router->generate('ad_homepage');
    }

    /**
     * @return array [route, options]
     */
    private function getLayDeputyHomepage($user, $enabledLastAccessedUrl)
    {
        if (!$user->hasDetails()) {
            return $this->router->generate('user_details');
        }

        if (!$user->hasClients()) {
            return $this->router->generate('client_add');
        }

        $clients = $user->getClients();

        $client = $clients[0];
        if (!$client->hasDetails()) {
            return $this->router->generate('client_add');
        }

//        if (!$user->hasReports()) {
//            return $this->router->generate('report_create', ['clientId' => $clients[0]->getId()]);
//        }

        if ($enabledLastAccessedUrl && $lastUsedUri = $this->getLastAccessedUrl()) {
            return $lastUsedUri;
        }

        return $this->router->generate('index-odr');

//        $reportIds = $clients[0]->getReports();
//
//        foreach ($reportIds as $reportId) {
//            $report = $this->restClient->get("report/{$reportId}", 'Report', ['query' => ['groups' => ['basic']]]);
//
//            if (!$report->getSubmitted()) {
//                return $this->router->generate('report_overview', ['reportId' => $reportId]);
//            }
//        }

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
            if ($securityContext->isGranted('ROLE_ADMIN')) {
                return $this->router->generate('admin_homepage');
            }
            if ($securityContext->isGranted('ROLE_AD')) {
                return $this->router->generate('ad_homepage');
            }

            return $this->router->generate('login');
        }

        // deputy: if logged, redirect to overview pages
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->getLayDeputyHomepage($this->getLoggedUser(), false);
        }

        return false;
    }
}
