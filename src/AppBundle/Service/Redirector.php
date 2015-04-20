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

    
    public function getUserFirstPage()
    {
        $user = $this->security->getToken()->getUser();
        $clients = $user->getClients();

        $route = 'access_denied';
        $options = [];
        
        
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $route = 'admin_homepage';
        } elseif ($this->security->isGranted('ROLE_LAY_DEPUTY')) {
            
            if (!$user->hasDetails()) {
                $route = 'user_details';
            }  else if(!$user->hasClients()) {
                $route = 'client_add';
            }else if(!$user->hasReports()){
                $route = 'report_create';
                $options = [ 'clientId' => $clients[0]['id']];
            }else if ($lastUsedUri = $this->getLastAccessedUrl()) {
                return $lastUsedUri;
            } else {
                $route = "report_overview";
                $options = [ 'reportId' => $clients[0]['reports'][0] ];
            }
        }
        
        return $this->router->generate($route, $options);
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
