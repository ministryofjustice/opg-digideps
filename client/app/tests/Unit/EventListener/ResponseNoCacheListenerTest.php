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

/**
 * Act on session on each request.
 */
class ResponseNoCacheListenerTest extends TestCase
{
    private const array EXPECTED_SET_CALLS = [
        1 => ['Cache-Control', 'no-cache, no-store, must-revalidate'],
        2 => ['Pragma', 'no-cache'],
        3 => ['Expires', '0'],
        4 => ['X-Session-Safe-Id', 'abc123'],
    ];

    public function testOnKernelResponseSetsNoCacheHeadersAndSessionSafeId(): void
    {
        $headers = self::createMock(ResponseHeaderBag::class);

        $invocationMatcher = self::exactly(count(self::EXPECTED_SET_CALLS));
        $headers->expects($invocationMatcher)
            ->method('set')
            ->willReturnCallback(function ($name, $value) use ($invocationMatcher) {
                $invocationNumber = $invocationMatcher->getInvocationCount();
                self::assertEquals(self::EXPECTED_SET_CALLS[$invocationNumber][0], $name);
                self::assertEquals(self::EXPECTED_SET_CALLS[$invocationNumber][1], $value);
            });

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

        $kernel = self::createMock(KernelInterface::class);

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $object = new ResponseNoCacheListener();
        $object->onKernelResponse($event);
    }

    public function testOnKernelResponseWithoutSessionSafeIdDoesNotSetHeader(): void
    {
        $headers = self::createMock(ResponseHeaderBag::class);

        // we only expect three headers to be set, not X-Session-Safe-Id
        $invocationMatcher = self::exactly(count(self::EXPECTED_SET_CALLS) - 1);
        $headers->expects($invocationMatcher)
            ->method('set')
            ->willReturnCallback(function ($name, $value) use ($invocationMatcher) {
                $invocationNumber = $invocationMatcher->getInvocationCount();

                self::assertNotEquals('X-Session-Safe-Id', $name, 'X-Session-Safe-Id should never be set');

                self::assertEquals(self::EXPECTED_SET_CALLS[$invocationNumber][0], $name);
                self::assertEquals(self::EXPECTED_SET_CALLS[$invocationNumber][1], $value);
            });

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

        $kernel = self::createMock(KernelInterface::class);

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $object = new ResponseNoCacheListener();
        $object->onKernelResponse($event);
    }
}
