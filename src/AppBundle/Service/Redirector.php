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
        'user_view',
        'report_overview',
        'account',
        'accounts',
        'contacts',
        'decisions',
        'assets',
        'report_declaration',
        'report_add_further_info',
        'report_submit_confirmation',
        'client_home',
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
    public function getUserFirstPage($enabledLastAccessedUrl = true)
    {
        $user = $this->security->getToken()->getUser();

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->getAdminHomepage();
        } elseif ($this->security->isGranted('ROLE_LAY_DEPUTY')) {
            return $this->getLayDeputyHomepage($user, $enabledLastAccessedUrl);
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
     * @return array [route, options]
     */
    private function getLayDeputyHomepage($user, $enabledLastAccessedUrl)
    {
        if (!$user->hasDetails()) {
             return $this->router->generate('user_details');
        }
        
        if(!$user->hasClients()) {
             return $this->router->generate('client_add');
        }
        
        $clients = $user->getClients();
        
        if(!$user->hasReports()){
            return $this->router->generate('report_create', [ 'clientId' => $clients[0]->getId()]);
        }
        
        if ($enabledLastAccessedUrl && $lastUsedUri = $this->getLastAccessedUrl()) {
            
            return $lastUsedUri;
        }
        
        return $this->router->generate('client_home');
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
    
    public function removeLastAccessedUrl()
    {
        $this->session->remove('_security.secured_area.target_path');
    }
}
