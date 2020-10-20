<?php

namespace AppBundle\EventListener;

use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\RestClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutListener implements LogoutSuccessHandlerInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RestClient
     */
    private $restClient;

    public function __construct(TokenStorageInterface $tokenStorage, RestClientInterface $restClient, RouterInterface $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->restClient = $restClient;
        $this->router = $router;
    }

    public function onLogoutSuccess(Request $request)
    {
        if ($this->tokenStorage->getToken() instanceof UsernamePasswordToken) {
            $this->restClient->logout();
        }

        $request->getSession()->set('loggedOutFrom', 'logoutPage');

        $response = new RedirectResponse($this->router->generate('login'));

        return $response;
    }
}
