<?php

namespace Tests\OPG\Digideps\Frontend\Unit\EventListener;

use OPG\Digideps\Frontend\EventListener\SessionListener;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Act on session on each request.
 */
class SessionListenerTest extends TestCase
{
    private RequestEvent&MockObject $event;
    private Router&MockObject $router;
    private LoggerInterface&MockObject $logger;

    public function setUp(): void
    {
        $this->event = $this->createMock(RequestEvent::class);
        $this->router = $this->createMock(Router::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testOnKernelRequestNoMasterWrongCtor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SessionListener($this->router, $this->logger, ['idleTimeout' => 0]);
    }

    public function testOnKernelRequestNoMasterReq(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $this->event->method('getRequestType')->willReturn(HttpKernelInterface::SUB_REQUEST);
        $this->assertEquals('no-master-request', $object->onKernelRequest($this->event));
    }

    public function testOnKernelRequestNoSession(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $event = $this->createMock(RequestEvent::class);
        $event->method('getRequestType')->willReturn(HttpKernelInterface::MAIN_REQUEST);
        $event->method('getRequest')->willReturn(new Request());
        $this->assertEquals('no-session', $object->onKernelRequest($event));
    }

    public function testOnKernelRequestSessionNotInitialisedLastUsed(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $event = $this->createMock(RequestEvent::class);

        $event->method('getRequestType')->willReturn(HttpKernelInterface::MAIN_REQUEST);
        $session = $this->createMock(SessionInterface::class);
        $session->method('getMetadataBag')->willReturn(new MetadataBag());
        $request = new Request();
        $request->setSession($session);
        $event->method('getRequest')->willReturn($request);
        $this->assertEquals('no-timeout', $object->onKernelRequest($event));
    }

    public function testOnKernelRequestNoLastUsed(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $event = $this->createMock(RequestEvent::class);

        $event->method('getRequestType')->willReturn(HttpKernelInterface::MAIN_REQUEST);
        $session = $this->createMock(SessionInterface::class);
        $session->method('getMetadataBag')->willReturn(new MetadataBag());
        $request = new Request();
        $request->setSession($session);
        $event->method('getRequest')->willReturn($request);

        $this->assertEquals('no-timeout', $object->onKernelRequest($event));
    }

    public static function provider(): array
    {
        return [
            [1500, 0, 0],
            [1500, -10, 0],
            [1500, -1490, 0], // close to expire

            [1500, -1500 - 10, 1], // expired 10 sec ago
            [1500, -1500 - 25 * 3600, 1], // expired 25h ago
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testOnKernelRequest(int $idleTimeout, int $lastUsedRelativeToCurrentTime, int $callsToManualExpire): void
    {
        $event = $this->createMock(RequestEvent::class);
        $router = $this->createMock(Router::class);
        $logger = $this->createMock(LoggerInterface::class);
        $object = new SessionListener($router, $logger, ['idleTimeout' => $idleTimeout]);

        $event->method('getRequestType')->willReturn(HttpKernelInterface::MAIN_REQUEST);
        $metadata = $this->createMock(MetadataBag::class);
        $session = $this->createMock(SessionInterface::class);
        $session->method('getMetadataBag')->willReturn($metadata);
        $request = $this->createMock(Request::class);
        $request->method('getSession')->willReturn($session);
        $request->method('hasSession')->willReturn(true);
        $event->method('getRequest')->willReturn($request);

        $metadata->method('getCreated')->willReturn(time());
        $metadata->method('getLastUsed')->willReturn(time() + $lastUsedRelativeToCurrentTime);

        // expectations
        $logger->expects($this->exactly($callsToManualExpire))->method('notice');
        $session->expects($this->exactly($callsToManualExpire))->method('invalidate');
        $session->expects($this->exactly($callsToManualExpire * 2))->method('set')->willReturnCallback(function (string $key, string $value) {
            $this->assertSame($value, match ($key) {
                '_security.secured_area.target_path' => 'URI',
                'loggedOutFrom' => 'timeout',
                default => null
            });
        });

        $event->expects($this->exactly($callsToManualExpire))->method('setResponse')->with(new IsInstanceOf(RedirectResponse::class));
        $event->expects($this->exactly($callsToManualExpire))->method('stopPropagation');
        $router->expects($this->exactly($callsToManualExpire))->method('generate')->with('login', [], UrlGeneratorInterface::ABSOLUTE_PATH)->willReturn('/login/timeout');

        $request->expects($this->exactly($callsToManualExpire))->method('getUri')->willReturn('URI');

        $object->onKernelRequest($event);
    }
}
