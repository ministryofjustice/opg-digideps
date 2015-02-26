<?php
namespace AppBundle\EventListener;

use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Login listener
 */
class LoginEventListener
{
    protected $router;
    protected $security;
    protected $dispatcher;
    
    /**
     * 
     * @param Router $router
     * @param SecurityContext $security
     * @param EventDispatcher $dispatcher
     */
    public function __construct(Router $router, SecurityContext $security, EventDispatcher $dispatcher) 
    {
        $this->router = $router;
        $this->security = $security;
        $this->dispatcher = $dispatcher;
    }
    
    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->dispatcher->addListener(KernelEvents::RESPONSE, [ $this, 'onKernelResponse']);
    }
    
    /**
     * On login determine user role and redirect appropiately
     * 
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $user = $this->security->getToken()->getUser();
        
        $route = 'access_denied';
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $route = 'admin_homepage';
        } elseif ($this->security->isGranted('ROLE_LAY_DEPUTY')) {
            /*if (!$user->hasDetails()) {
                $route = 'user_details';
            } else if (!$user->hasClient()) { 
                $route = 'client_add';
            } else if (!$user->getClient()->hasReport()) {
                $route = 'report_create';
            }else {
                $route = 'homepage'; // TODO use dashboard when implemented
            }*/
            $route = 'client_add';
        }

        $event->getResponse()->headers->set('Location', $this->router->generate($route));
    }
}