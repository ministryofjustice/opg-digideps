<?php

namespace App\EventListener;

//use Symfony\Component\EventDispatcher\EventDispatcher;
use App\Service\Redirector;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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
     */
    public function __construct(EventDispatcherInterface $dispatcher, Redirector $Redirector)
    {
        $this->dispatcher = $dispatcher;
        $this->redirector = $Redirector;
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
    }
}
