<?php
namespace AppBundle\Service;

use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;
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
     * Routes the user can be redirected to, if accessed before timeout
     * 
     * @var array
     */
    private $redirectableRoutes = [
        'user_details',
        'report_overview',
        'account',
        'accounts',
        'contacts',
        'decisions',
        'assets',
        'report_declaration',
    ];
    
    /**
     * @param \AppBundle\Service\SecurityContext $security
     * @param type $router
     */
    public function __construct(SecurityContextInterface $security, RouterInterface $router, Session $session)
    {
        $this->security = $security;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function getUserFirstPage()
    {
        $user = $this->security->getToken()->getUser();

        if ($this->security->isGranted('ROLE_ADMIN')) {
            list($route, $options) = $this->getAdminHomepage();
        } elseif ($this->security->isGranted('ROLE_LAY_DEPUTY')) {
            list($route, $options) = $this->getLayDeputyHomepage($user);
        } else {
             $route = 'access_denied';
             $options = [];
        }
        
        return $this->router->generate($route, $options);
    }
    
    /**
     * @return array [route, options]
     */
    private function getAdminHomepage()
    {
        return ['admin_homepage', []];
    }
    
    /**
     * @return array [route, options]
     */
    private function getLayDeputyHomepage($user)
    {
        if (!$user->hasDetails()) {
            return ['user_details', []];
        }
        
        if(!$user->hasClients()) {
            return ['client_add', []];
        }
        
        $clients = $user->getClients();
        
        if(!$user->hasReports()){
            return ['report_create', [ 'clientId' => $clients[0]['id']]];
        }
        
        if ($lastUsedUri = $this->getLastAccessedUrl()) {
            return $lastUsedUri;
        }
        
        return ["report_overview", [ 'reportId' => $clients[0]['reports'][0] ]];
    }
    
   
    /**
     * @return boolean|string
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
        } catch (ResourceNotFoundException $e){
            return false;
        }
        
        if (in_array($route, $this->redirectableRoutes)) {
            return $lastUsedUrl;
        }
        
        return false;
    }
}
