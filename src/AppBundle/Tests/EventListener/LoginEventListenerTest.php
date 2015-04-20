<?php
namespace AppBundle\EventListener;


use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use AppBundle\Service\Redirector;

/**
 * Login listener
 */
class LoginEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function onSecurityInteractiveLogin()
    {
    }
    
    /**
     * @test
     */
    public function onKernelResponse()
    {
    }
}