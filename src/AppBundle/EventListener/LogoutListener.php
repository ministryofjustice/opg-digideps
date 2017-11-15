<?php

namespace AppBundle\EventListener;

use AppBundle\Service\Client\RestClient;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutListener implements LogoutSuccessHandlerInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(TokenStorageInterface $tokenStorage, RestClient $restClient, Router $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->restClient = $restClient;
        $this->router = $router;
    }

    public function onLogoutSuccess(Request $request)
    {
        if ($this->tokenStorage->getToken()) {
            $this->restClient->logout();
        }

        $request->getSession()->set('loggedOutFrom', 'logoutPage');

        $response = new RedirectResponse($this->router->generate('login'));

        return $response;
    }
}
