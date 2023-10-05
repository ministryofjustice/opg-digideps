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

/**
 * Act on session on each request.
 */
class ResponseNoCacheListenerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function onKernelResponse()
    {
        /** @var ObjectProphecy|ResponseHeaderBag $headers */
        $headers = self::prophesize(ResponseHeaderBag::class);

        $headers->set('Cache-Control', 'no-cache, no-store, must-revalidate')->shouldBeCalled();
        $headers->set('Pragma', 'no-cache')->shouldBeCalled();
        $headers->set('Expires', '0')->shouldBeCalled();

        /** @var ObjectProphecy|Response $response */
        $response = self::prophesize(Response::class);
        $response->headers = $headers->reveal();

        $kernel = self::prophesize(KernelInterface::class);
        $request = self::prophesize(Request::class);

        $event = new ResponseEvent($kernel->reveal(), $request->reveal(), HttpKernelInterface::MAIN_REQUEST, $response->reveal());

        $object = new ResponseNoCacheListener();
        $object->onKernelResponse($event);
    }
}
