<?php

namespace App\EventListener;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
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
    use ProphecyTrait;

    /**
     * @test
     */
    public function testOnKernelResponseSetsNoCacheHeadersAndSessionSafeId()
    {
        /** @var ObjectProphecy|ResponseHeaderBag $headers */
        $headers = self::prophesize(ResponseHeaderBag::class);

        $headers->set('Cache-Control', 'no-cache, no-store, must-revalidate')->shouldBeCalled();
        $headers->set('Pragma', 'no-cache')->shouldBeCalled();
        $headers->set('Expires', '0')->shouldBeCalled();
        $headers->set('X-Session-Safe-Id', 'abc123')->shouldBeCalled();

        /** @var ObjectProphecy|Response $response */
        $response = self::prophesize(Response::class);
        $response->headers = $headers->reveal();

        /** @var ObjectProphecy|SessionInterface $session */
        $session = self::prophesize(SessionInterface::class);
        $session->has('session_safe_id')->willReturn(true);
        $session->get('session_safe_id')->willReturn('abc123');

        /** @var ObjectProphecy|Request $request */
        $request = self::prophesize(Request::class);
        $request->getSession()->willReturn($session->reveal());

        /** @var ObjectProphecy|KernelInterface $kernel */
        $kernel = self::prophesize(KernelInterface::class);

        $event = new ResponseEvent(
            $kernel->reveal(),
            $request->reveal(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->reveal()
        );

        $object = new ResponseNoCacheListener();
        $object->onKernelResponse($event);
    }

    /**
     * @test
     */
    public function testOnKernelResponseWithoutSessionSafeIdDoesNotSetHeader()
    {
        /** @var ObjectProphecy|ResponseHeaderBag $headers */
        $headers = self::prophesize(ResponseHeaderBag::class);

        $headers->set('Cache-Control', 'no-cache, no-store, must-revalidate')->shouldBeCalled();
        $headers->set('Pragma', 'no-cache')->shouldBeCalled();
        $headers->set('Expires', '0')->shouldBeCalled();
        // session_safe_id header should NOT be set
        $headers->set('X-Session-Safe-Id', 'abc123')->shouldNotBeCalled();

        /** @var ObjectProphecy|Response $response */
        $response = self::prophesize(Response::class);
        $response->headers = $headers->reveal();

        /** @var ObjectProphecy|SessionInterface $session */
        $session = self::prophesize(SessionInterface::class);
        $session->has('session_safe_id')->willReturn(false);

        /** @var ObjectProphecy|Request $request */
        $request = self::prophesize(Request::class);
        $request->getSession()->willReturn($session->reveal());

        /** @var ObjectProphecy|KernelInterface $kernel */
        $kernel = self::prophesize(KernelInterface::class);

        $event = new ResponseEvent(
            $kernel->reveal(),
            $request->reveal(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->reveal()
        );

        $object = new ResponseNoCacheListener();
        $object->onKernelResponse($event);
    }
}
