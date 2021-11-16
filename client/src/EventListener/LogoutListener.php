<?php

namespace App\EventListener;

use App\Service\Client\RestClient;
use App\Service\Client\RestClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class LogoutListener implements LogoutSuccessHandlerInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage, private RestClientInterface $restClient, private RouterInterface $router)
    {
    }

    public function onLogoutSuccess(Request $request)
    {
        if ($this->tokenStorage->getToken() instanceof UsernamePasswordToken) {
            $this->restClient->logout();
        }

        $request->getSession()->set('loggedOutFrom', 'logoutPage');

        return new RedirectResponse($this->router->generate('login'));
    }
}
