<?php
namespace AppBundle\Service;

use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Session;

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
        
        if ($this->security->isGranted('ROLE_ADMIN') || ($this->security->isGranted('ROLE_LAY_DEPUTY'))) {
            if ($lastUsedUrl = $this->session->get('_security.secured_area.target_path')) {
                // avoid loops (might happend in browser misbehaving with redirects)
                $url = trim(parse_url($lastUsedUrl)['path'], '/');
                $isHomepage = empty($url);
                if (!$isHomepage) {
                    return $lastUsedUrl;
                }
            }
        }
        
        
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
            }else{
                $route = "report_overview";
                $options = [ 'reportId' => $clients[0]['reports'][0] ];
            }
        }
        
        return $this->router->generate($route, $options);
    }
}
