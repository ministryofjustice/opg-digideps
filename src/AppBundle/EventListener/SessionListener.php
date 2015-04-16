<?php
namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Act on session on each request
 * 
 */
class SessionListener
{
    /**
     * @var integer
     */
    private $idleTimeout;  
    
    /**
     * @var Router
     */
    private $router; 

    /**
     * @param array $options keys: idleTimeout (seconds)
     * @throws \InvalidArgumentException
     */
    public function __construct(Router $router, array $options)
    {
        $this->router = $router;
        $this->idleTimeout = (int)$options['idleTimeout'];
        if ($this->idleTimeout < 30) {
            throw new \InvalidArgumentException(__CLASS__ . " :session timeout cannot be lower than 30 seconds");
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
        
        $session = $event->getRequest()->getSession();
        $lastUsed = (int)$session->getMetadataBag()->getLastUsed();
        if (!$lastUsed) {
            return 'no-last-used';
        }
        
        $idleTime = time() - $lastUsed;
        $hasReachedIdleTimeout = $idleTime > $this->idleTimeout;
        
        if ($hasReachedIdleTimeout) {
            //Invalidate the current session and throw an exception
            $session->invalidate();
            $event->setResponse(new RedirectResponse($this->router->generate('login', ['options'=> 'timeout'])));
            $event->stopPropagation();
            
            return;
        }
        
        return 'session-valid';
    }
    
}