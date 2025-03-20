<?php

namespace App\EventSubscriber;

use App\Service\Client\RestClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
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
     * @var RestClientInterface
     */
    private $restClient;

    public function __construct(TokenStorageInterface $tokenStorage, RestClientInterface $restClient, RouterInterface $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->restClient = $restClient;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();

        file_put_contents('php://stderr', print_r('DEBUG - LOGOUT SUCCESS', true));
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
