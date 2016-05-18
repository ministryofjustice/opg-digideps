<?php

namespace AppBundle\EventListener;

//use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use AppBundle\Service\Redirector;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /**
     * @param EventDispatcher $dispatcher
     * @param Redirector      $Redirector
     */
    public function __construct(EventDispatcherInterface $dispatcher, Redirector $Redirector)
    {
        $this->dispatcher = $dispatcher;
        $this->redirector = $Redirector;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $this->dispatcher->addListener(KernelEvents::RESPONSE, [$this, 'onKernelResponse']);
    }

    /**
     * On login determine user role and redirect appropiately.
     * 
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $redirectUrl = $this->redirector->getFirstPageAfterLogin();

        $this->redirector->removeLastAccessedUrl(); //avoid this URL to be used a the next login

        $event->getResponse()->headers->set('Location', $redirectUrl);
    }
}
