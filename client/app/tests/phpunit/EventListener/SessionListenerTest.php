<?php

namespace App\EventListener;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Act on session on each request.
 */
class SessionListenerTest extends TestCase
{
    public function setUp(): void
    {
        $this->event = m::mock('Symfony\Component\HttpKernel\Event\RequestEvent');
        $this->router = m::mock('Symfony\Bundle\FrameworkBundle\Routing\Router');
        $this->logger = m::mock('Symfony\Bridge\Monolog\Logger');
    }

    /**
     * @test
     */
    public function onKernelRequestNoMasterWrongCtor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SessionListener($this->router, $this->logger, ['idleTimeout' => 0]);
    }

    /**
     * @test
     */
    public function onKernelRequestNoMasterReq(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $this->event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::SUB_REQUEST);
        $this->assertEquals('no-master-request', $object->onKernelRequest($this->event));
    }

    /**
     * @test
     */
    public function onKernelRequestNoSession(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $event = m::mock('Symfony\Component\HttpKernel\Event\RequestEvent');
        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MAIN_REQUEST);
        $event->shouldReceive('getRequest->hasSession')->andReturn(false);
        $this->assertEquals('no-session', $object->onKernelRequest($event));
    }

    /**
     * @test
     */
    public function onKernelRequestSessionNotInitialisedLastUsed(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $event = m::mock(RequestEvent::class);

        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MAIN_REQUEST);
        $event->shouldReceive('getRequest->hasSession')->andReturn(true);

        $event->shouldReceive('getRequest->getSession->getMetadataBag->getCreated')->andReturn(0);
        $this->assertEquals('no-timeout', $object->onKernelRequest($event));
    }

    /**
     * @test
     */
    public function onKernelRequestNoLastUsed()
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $event = m::mock(RequestEvent::class);

        $event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MAIN_REQUEST);
        $event->shouldReceive('getRequest->hasSession')->andReturn(true);

        $event->shouldReceive('getRequest->getSession->getMetadataBag->getCreated')->andReturn(time());
        $event->shouldReceive('getRequest->getSession->getMetadataBag->getLastUsed')->andReturn(0);
        $this->assertEquals('no-timeout', $object->onKernelRequest($event));
    }

    public static function provider(): array
    {
        return [
            [1500, 0, 0],
            [1500, -10, 0],
            [1500, -1490, 0], //close to epire

            [1500, -1500 - 10, 1], // expired 10 sec ago
            [1500, -1500 - 25 * 3600, 1], //expired 25h ago
        ];
    }

    /**
     * @test
     * @dataProvider provider
     * @doesNotPerformAssertions
     */
    public function onKernelRequest($idleTimeout, $lastUsedRelativeToCurrentTime, $callsToManualExpire): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => $idleTimeout]);

        $this->event->shouldReceive('getRequestType')->andReturn(HttpKernelInterface::MAIN_REQUEST);
        $this->event->shouldReceive('getRequest->hasSession')->andReturn(true);

        $this->event->shouldReceive('getRequest->getSession->getMetadataBag->getCreated')->andReturn(time());
        $this->event->shouldReceive('getRequest->getSession->getMetadataBag->getLastUsed')->andReturn(time() + $lastUsedRelativeToCurrentTime);

        // expectations
        $this->logger->shouldReceive('notice')->times($callsToManualExpire);
        $this->event->shouldReceive('getRequest->getSession->invalidate')->times($callsToManualExpire);
        $this->event->shouldReceive('getRequest->getSession->set')->times($callsToManualExpire)->with('_security.secured_area.target_path', 'URI');
        $this->event->shouldReceive('getRequest->getSession->set')->times($callsToManualExpire)->with('loggedOutFrom', 'timeout');

        $this->event->shouldReceive('setResponse')->times($callsToManualExpire)->with(m::type('Symfony\Component\HttpFoundation\RedirectResponse'));
        $this->event->shouldReceive('stopPropagation')->times($callsToManualExpire);
        $this->router->shouldReceive('generate')->with('login')->times($callsToManualExpire)->andReturn('/login/timeout');

        $this->event->shouldReceive('getRequest->getUri')->times($callsToManualExpire)->andReturn('URI');

        $object->onKernelRequest($this->event);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
