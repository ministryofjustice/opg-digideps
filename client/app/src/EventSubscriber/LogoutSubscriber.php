<?php

namespace App\EventSubscriber;

use App\Service\Client\RestClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RestClientInterface $restClient,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();

        if ($this->tokenStorage->getToken() instanceof UsernamePasswordToken) {
            $this->restClient->logout();
        }

        $notPrimaryAccount = $request->query->get('notPrimaryAccount');

        if (!$notPrimaryAccount) {
            $request->getSession()->set('loggedOutFrom', 'logoutPage');
        }
        $request->getSession()->set('fromLogoutPage', 1);

        $response = new RedirectResponse('/login');

        $event->setResponse($response);
    }
}
