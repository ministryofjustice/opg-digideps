<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventListener;

use OPG\Digideps\Frontend\EventListener\ResponseNoCacheListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Act on session on each request.
 */
class ResponseNoCacheListenerTest extends TestCase
{
    public function testOnKernelResponseSetsNoCacheHeadersAndSessionSafeId(): void
    {
        $headers = self::createMock(ResponseHeaderBag::class);

        /** @var array<string, mixed> $actualSetCalls */
        $actualSetCalls = [];

        $headers->expects(self::exactly(5))
            ->method('set')
            ->willReturnCallback(
                function (string $name, mixed $value) use (&$actualSetCalls): void {
                    $actualSetCalls[$name] = $value;
                }
            );

        $response = self::createMock(Response::class);
        $response->headers = $headers;

        $session = self::createMock(SessionInterface::class);
        $session->expects(self::once())
            ->method('has')
            ->with('session_safe_id')
            ->willReturn(true);

        $session->expects(self::once())
            ->method('get')
            ->with('session_safe_id')
            ->willReturn('abc123');

        $request = self::createMock(Request::class);
        $request->expects(self::once())
            ->method('getSession')
            ->willReturn($session);

        $security = self::createMock(Security::class);
        $security->expects(self::once())
            ->method('getUser')
            ->willReturn(self::createMock(UserInterface::class));

        $kernel = self::createMock(KernelInterface::class);

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $listener = new ResponseNoCacheListener($security);
        $listener->onKernelResponse($event);

        self::assertEqualsCanonicalizing(
            [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Authenticated' => '1',
                'X-Session-Safe-Id' => 'abc123',
            ],
            $actualSetCalls
        );
    }

    public function testOnKernelResponseWithoutSessionSafeIdDoesNotSetHeader(): void
    {
        $headers = self::createMock(ResponseHeaderBag::class);

        /** @var array<string, mixed> $actualSetCalls */
        $actualSetCalls = [];

        $headers->expects(self::exactly(4))
            ->method('set')
            ->willReturnCallback(
                function (string $name, mixed $value) use (&$actualSetCalls): void {
                    $actualSetCalls[$name] = $value;
                }
            );

        $response = self::createMock(Response::class);
        $response->headers = $headers;

        $session = self::createMock(SessionInterface::class);
        $session->expects(self::once())
            ->method('has')
            ->with('session_safe_id')
            ->willReturn(false);

        $request = self::createMock(Request::class);
        $request->expects(self::once())
            ->method('getSession')
            ->willReturn($session);

        $security = self::createMock(Security::class);
        $security->expects(self::once())
            ->method('getUser')
            ->willReturn(null);

        $kernel = self::createMock(KernelInterface::class);

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $listener = new ResponseNoCacheListener($security);
        $listener->onKernelResponse($event);

        self::assertEqualsCanonicalizing(
            [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Authenticated' => '0',
            ],
            $actualSetCalls
        );

        self::assertArrayNotHasKey(
            'X-Session-Safe-Id',
            $actualSetCalls
        );
    }
}
