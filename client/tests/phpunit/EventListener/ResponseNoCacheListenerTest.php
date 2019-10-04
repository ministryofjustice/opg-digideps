<?php

namespace AppBundle\EventListener;

use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * Act on session on each request.
 */
class ResponseNoCacheListenerTest extends TestCase
{
    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function onKernelResponse()
    {
        $headers = m::mock('Symfony\Component\HttpFoundation');
        $headers->shouldReceive('set')->once()->with('Cache-Control', 'no-cache, no-store, must-revalidate');
        $headers->shouldReceive('set')->once()->with('Pragma', 'no-cache');
        $headers->shouldReceive('set')->once()->with('Expires', '0');

        $response = m::mock('Symfony\Component\HttpFoundation\Response');
        $response->headers = $headers;

        $event = m::mock('Symfony\Component\HttpKernel\Event\FilterResponseEvent');
        $event->shouldReceive('getResponse')->andReturn($response);

        $object = new ResponseNoCacheListener();
        $object->onKernelResponse($event);

        m::close();
    }
}
