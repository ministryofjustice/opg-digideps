<?php

declare(strict_types=1);

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
        $this->event = self::createMock(RequestEvent::class);
        $this->router = self::createMock(Router::class);
        $this->logger = self::createMock(LoggerInterface::class);
    }

    public function testOnKernelRequestNoMasterWrongCtor(): void
    {
        self::expectException(\InvalidArgumentException::class);
        new SessionListener($this->router, $this->logger, ['idleTimeout' => 0]);
    }

    public function testOnKernelRequestNoMasterReq(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $this->event->method('getRequestType')->willReturn(HttpKernelInterface::SUB_REQUEST);
        self::assertEquals('no-master-request', $object->onKernelRequest($this->event));
    }

    public function testOnKernelRequestNoSession(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $event = self::createMock(RequestEvent::class);
        $event->method('getRequestType')->willReturn(HttpKernelInterface::MAIN_REQUEST);
        $event->method('getRequest')->willReturn(new Request());
        self::assertEquals('no-session', $object->onKernelRequest($event));
    }

    public function testOnKernelRequestSessionNotInitialisedLastUsed(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $event = self::createMock(RequestEvent::class);

        $event->method('getRequestType')->willReturn(HttpKernelInterface::MAIN_REQUEST);
        $session = self::createMock(SessionInterface::class);
        $session->method('getMetadataBag')->willReturn(new MetadataBag());
        $request = new Request();
        $request->setSession($session);
        $event->method('getRequest')->willReturn($request);
        self::assertEquals('no-timeout', $object->onKernelRequest($event));
    }

    public function testOnKernelRequestNoLastUsed(): void
    {
        $object = new SessionListener($this->router, $this->logger, ['idleTimeout' => 600]);

        $event = self::createMock(RequestEvent::class);

        $event->method('getRequestType')->willReturn(HttpKernelInterface::MAIN_REQUEST);
        $session = self::createMock(SessionInterface::class);
        $session->method('getMetadataBag')->willReturn(new MetadataBag());
        $request = new Request();
        $request->setSession($session);
        $event->method('getRequest')->willReturn($request);

        self::assertEquals('no-timeout', $object->onKernelRequest($event));
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
        $event = self::createMock(RequestEvent::class);
        $router = self::createMock(Router::class);
        $logger = self::createMock(LoggerInterface::class);
        $object = new SessionListener($router, $logger, ['idleTimeout' => $idleTimeout]);

        $event->method('getRequestType')->willReturn(HttpKernelInterface::MAIN_REQUEST);
        $metadata = self::createMock(MetadataBag::class);
        $session = self::createMock(SessionInterface::class);
        $session->method('getMetadataBag')->willReturn($metadata);
        $request = self::createMock(Request::class);
        $request->method('getSession')->willReturn($session);
        $request->method('hasSession')->willReturn(true);
        $event->method('getRequest')->willReturn($request);

        $metadata->method('getCreated')->willReturn(time());
        $metadata->method('getLastUsed')->willReturn(time() + $lastUsedRelativeToCurrentTime);

        // expectations
        $logger->expects(self::exactly($callsToManualExpire))->method('notice');
        $session->expects(self::exactly($callsToManualExpire))->method('invalidate');
        $session->expects(self::exactly($callsToManualExpire * 2))->method('set')->willReturnCallback(function (string $key, string $value) {
            self::assertSame($value, match ($key) {
                '_security.secured_area.target_path' => 'URI',
                'loggedOutFrom' => 'timeout',
                default => null
            });
        });

        $event->expects(self::exactly($callsToManualExpire))->method('setResponse')->with(new IsInstanceOf(RedirectResponse::class));
        $event->expects(self::exactly($callsToManualExpire))->method('stopPropagation');
        $router->expects(self::exactly($callsToManualExpire))->method('generate')->with('login', [], UrlGeneratorInterface::ABSOLUTE_PATH)->willReturn('/login/timeout');

        $request->expects(self::exactly($callsToManualExpire))->method('getUri')->willReturn('URI');

        $object->onKernelRequest($event);
    }
}
