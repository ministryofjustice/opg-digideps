<?php
namespace AppBundle\EventListener;

use Mockery as m;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;

/**
 * Act on session on each request
 * 
 */
class SessionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function onKernelRequestNoMasterWrongCtor()
    {
       new SessionListener(['idleTimeout' => 0]);
    }
    
    /**
     * @test
     */
    public function onKernelRequestNoMasterReq()
    {
        $object = new SessionListener(['idleTimeout'=>600]);
        
        $event = m::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::SUB_REQUEST);
        $this->assertEquals('no-master-request', $object->onKernelRequest($event));
        
    }
    
     /**
     * @test
     */
    public function onKernelRequestNoSession()
    {
        $object = new SessionListener(['idleTimeout'=>600]);
        
        $event = m::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MASTER_REQUEST);
        $event->shouldReceive('getRequest->hasSession')->andReturn(false);
        $this->assertEquals('no-session', $object->onKernelRequest($event));
        
    }
    
    /**
     * @test
     */
    public function onKernelRequestNoLastUsed()
    {
        $object = new SessionListener(['idleTimeout'=>600]);
        
        $event = m::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        
        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MASTER_REQUEST);
        $event->shouldReceive('getRequest->hasSession')->andReturn(true);
         
        $event->shouldReceive('getRequest->getSession->getMetadataBag->getLastUsed')->andReturn(0);
        $this->assertEquals('no-last-used', $object->onKernelRequest($event));
        
    }
    
    
    public static function provider()
    {
        return [
            [1500, 0, false],
            [1500, -10, false],
            [1500, -1490, false], //close to epire
            
            [1500, -1500 - 10, true], // expired 10 sec ago
            [1500, -1500 - 25 * 3600, true], //expired 25h ago
        ];
    }
    
    /**
     * @test
     * @dataProvider provider
     */
    public function onKernelRequest($idleTimeout, $lastUsedRelativeToCurrentTime, $expectedExpire)
    {
        $object = new SessionListener(['idleTimeout'=>$idleTimeout]);
        
        $event = m::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent');
        
        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MASTER_REQUEST);
        $event->shouldReceive('getRequest->hasSession')->andReturn(true);
         
        $event->shouldReceive('getRequest->getSession->getMetadataBag->getLastUsed')->andReturn(time() + $lastUsedRelativeToCurrentTime);
        
        if ($expectedExpire) {
            $event->shouldReceive('getRequest->getSession->invalidate')->once();
            try {
                $object->onKernelRequest($event);
                 $this->fail('CredentialsExpiredException not thrown');
            } catch (CredentialsExpiredException $cee) {
            }
            
        } else {
            $this->assertEquals('session-valid', $object->onKernelRequest($event));
        }
        
    }
    
    public function tearDown()
    {
        m::close();
    }
}