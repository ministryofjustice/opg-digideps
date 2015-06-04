<?php

namespace AppBundle\EventListener;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogoutListener implements LogoutSuccessHandlerInterface
{

    private $security;
    private $router;
    private $memcached;


    public function __construct(SecurityContext $security, $router, \Memcached $memcached )
    {
        $this->security = $security;
        $this->router = $router;
        $this->memcached = $memcached;
    }

    public function onLogoutSuccess(Request $request)
    {
        $request->getSession()->set('loggedOutFrom', 'logoutPage');
        $this->memcached->flush();
        
        $response = new RedirectResponse($this->router->generate('login'));

        return $response;
    }

}