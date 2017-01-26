<?php

namespace AppBundle\EventListener;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Redirect to login page when session is Idle for more than `idleTimeout` amount in seconds.
 */
class SessionListener
{
    const SESSION_FLAG_KEY = 'hasIdleTimedOut';

    /**
     * @var int
     */
    private $idleTimeout;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param array $options keys: idleTimeout (seconds)
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Router $router, Logger $logger, array $options)
    {
        $this->router = $router;
        $this->logger = $logger;
        $this->idleTimeout = (int) $options['idleTimeout'];

        if ($this->idleTimeout < 5) {
            throw new \InvalidArgumentException(__CLASS__.' :session timeout cannot be lower than 5 seconds');
        }
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        // Only operate on the master request and when there is a session
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return 'no-master-request';
        }
        if (!$event->getRequest()->hasSession()) {
            return 'no-session';
        }
        if ($this->hasReachedTimeout($event)) {
            $this->handleTimeout($event);
            $this->logger->notice('Timeout reached, user redirected to login page');

            return;
        }

        return 'no-timeout';
    }

    private function hasReachedTimeout(GetResponseEvent $event)
    {
        $session = $event->getRequest()->getSession();
        $lastUsed = (int) $session->getMetadataBag()->getLastUsed();
        if (!$lastUsed) {
            return false;
        }
        $idleTime = time() - $lastUsed;

        return $idleTime > $this->idleTimeout;
    }

    private function handleTimeout(GetResponseEvent $event)
    {
        $session = $event->getRequest()->getSession();
        //Invalidate the current session and throw an exception
        $session->invalidate();
        $response = new RedirectResponse($this->router->generate('login'));
        $event->setResponse($response);
        $event->stopPropagation();
        $session->set('loggedOutFrom', 'timeout');
        $session->set('_security.secured_area.target_path', $event->getRequest()->getUri());
    }
}
