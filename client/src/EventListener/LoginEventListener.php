<?php

namespace App\EventListener;

//use Symfony\Component\EventDispatcher\EventDispatcher;
use App\Service\Client\TokenStorage\RedisStorage;
use App\Service\Redirector;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Login listener.
 */
class LoginEventListener
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    /**
     * @var Redirector
     */
    protected $redirector;
    private HttpClientInterface $phpApiClient;
    private RedisStorage $redisStorage;

    /**
     * @param EventDispatcher $dispatcher
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        Redirector $Redirector,
        HttpClientInterface $phpApiClient,
        RedisStorage $redisStorage
    ) {
        $this->dispatcher = $dispatcher;
        $this->redirector = $Redirector;
        $this->phpApiClient = $phpApiClient;
        $this->redisStorage = $redisStorage;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->dispatcher->addListener(KernelEvents::RESPONSE, [$this, 'onKernelResponse']);
    }

    /**
     * On login determine user role and redirect appropiately.
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $redirectUrl = $this->redirector->getFirstPageAfterLogin($event->getRequest()->getSession());

        $this->redirector->removeLastAccessedUrl(); //avoid this URL to be used a the next login

        $event->getResponse()->headers->set('Location', $redirectUrl);

        // 'login-context' determines a one-time message that may have been displayed during login. Remove to prevent showing again.
        $event->getRequest()->getSession()->remove('login-context');

        // Get public key from API
        $jwkResponse = $this->phpApiClient->request('GET', 'jwk-public-key');
        $jwks = json_decode($jwkResponse->getContent(), true);

        if ($event->getResponse()->headers->get('jwt')) {
            // Get JWT and save into session for user
            $jwt = json_decode($event->getResponse()->headers->get('jwt'), true)['token'];

            try {
                $decoded = JWT::decode($jwt, JWK::parseKeySet($jwks), ['RS256']);
            } catch (Throwable) {
                throw new RuntimeException('Problems authenticating - try again');
            }

            $userId = (array) $decoded['userId'];
            $this->redisStorage->set($userId, $jwt);
        }
    }
}
