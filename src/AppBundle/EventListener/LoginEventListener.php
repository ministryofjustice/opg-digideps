<?php
namespace AppBundle\EventListener;

use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;


class LoginEventListener
{
    protected $router;
    protected $security;
    protected $dispatcher;
    
    public function __construct(Router $router, SecurityContext $security, EventDispatcher $dispatcher) 
    {
        $this->router = $router;
        $this->security = $security;
        $this->dispatcher = $dispatcher;
    }
    
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->dispatcher->addListener(KernelEvents::RESPONSE, [ $this, 'onKernelResponse']);
    }
    
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if($this->security->isGranted('ROLE_ADMIN')){
            $event->getResponse()->headers->set('Location', $this->router->generate('admin_homepage'));
        }elseif($this->security->isGranted('ROLE_LAY_DEPUTY')){
            $event->getResponse()->headers->set('Location', $this->router->generate('user_details'));
        }
    }
}