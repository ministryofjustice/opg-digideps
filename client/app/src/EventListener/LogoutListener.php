<?php

namespace App\EventListener;

use App\Service\Client\RestClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    private TokenStorageInterface $tokenStorage;
    private RestClientInterface $restClient;
    private RouterInterface $router;

    public function __construct(TokenStorageInterface $tokenStorage, RestClientInterface $restClient, RouterInterface $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->restClient = $restClient;
        $this->router = $router;
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();

        // Handle token-based logout
        if ($this->tokenStorage->getToken() instanceof UsernamePasswordToken) {
            $this->restClient->logout();
        }

        // Handle session value setting
        $notPrimaryAccount = $request->query->get('notPrimaryAccount');

        if (!$notPrimaryAccount) {
            $request->getSession()->set('loggedOutFrom', 'logoutPage');
        }

        // Set the redirect response
        $response = new RedirectResponse($this->router->generate('login'));
        $event->setResponse($response);
    }
}
