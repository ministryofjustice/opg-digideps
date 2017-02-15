<?php

namespace AppBundle\EventListener;

use AppBundle\Service\Client\RestClient;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutListener implements LogoutSuccessHandlerInterface
{
    /**
     * @var SecurityContext
     */
    private $security;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(SecurityContext $security, RestClient $restClient, Router $router)
    {
        $this->security = $security;
        $this->restClient = $restClient;
        $this->router = $router;
    }

    public function onLogoutSuccess(Request $request)
    {
        if ($this->security->getToken()) {
            $this->restClient->logout();
        }

        $request->getSession()->set('loggedOutFrom', 'logoutPage');

        $response = new RedirectResponse($this->router->generate('login'));

        return $response;
    }
}
