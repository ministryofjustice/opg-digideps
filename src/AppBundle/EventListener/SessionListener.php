<?php
namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;

/**
 * Redirect to login page when session is Idle for more than `idleTimeout` amount in seconds
 * 
 */
class SessionListener
{
    const SESSION_FLAG_KEY = 'hasIdleTimedOut';
    
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
            $this->hasReachedTimeout($event);
            return;
        }
        
        return 'session-valid';
    }
    
    private function hasReachedTimeout(GetResponseEvent $event)
    {
        $session = $event->getRequest()->getSession();
        //Invalidate the current session and throw an exception
        $session->invalidate();
        $response = new RedirectResponse($this->router->generate('login'));
        $event->setResponse($response);
        $event->stopPropagation();
        $session->set(self::SESSION_FLAG_KEY, 1);
        // save 
        $session->set('_security.secured_area.target_path', $event->getRequest()->getUri());
    }
    
    /**
     * @param Request $request
     * @return boolean
     */
    public static function hasIdleTimeoutOcccured(Request $request)
    {
        return (boolean)$request->getSession()->get(self::SESSION_FLAG_KEY);
    }
    
}