<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\RequestIdLoggerProcessor;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestIdLoggerProcessorTest extends TestCase
{
    private Container $container;
    private RequestStack $reqStack;
    private RequestIdLoggerProcessor $object;
    private array $record = ['key1' => 'abc', 'key2' => 2];

    public function setUp(): void
    {
        $this->container = m::mock(Container::class);
        $this->reqStack = m::mock(RequestStack::class);

        $this->object = new RequestIdLoggerProcessor($this->container);
    }

    public function testProcessRecordNoReqStack(): void
    {
        $this->container->shouldReceive('has')->with('request_stack')->andReturn(false);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn(null);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequest(): void
    {
        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn(null);
        $this->container->shouldReceive('has')->with('request_stack')->andReturn(false);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequestId(): void
    {
        $request = new Request();
        $request->headers = new ParameterBag();

        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $this->reqStack->shouldReceive('has')->with('x-request-id')->andReturn(false);
        $this->container->shouldReceive('has')->with('request_stack')->andReturn(true);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasRequestId(): void
    {
        $request = new Request();
        $request->headers = new ParameterBag();
        $request->headers->set('x-aws-request-id', 'THIS_IS_THE_REQUEST_ID');

        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $this->reqStack->shouldReceive('has')->with('x-aws-request-id')->andReturn(true);
        $this->reqStack->shouldReceive('get')->with('x-aws-request-id')->andReturn('THIS_IS_THE_REQUEST_ID');
        $this->reqStack->shouldReceive('get')->with('x-aws-request-id')->andReturn('THIS_IS_THE_REQUEST_ID');
        $this->container->shouldReceive('has')->with('request_stack')->andReturn(true);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record + ['extra' => ['aws_request_id' => 'THIS_IS_THE_REQUEST_ID']], $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasSafeId(): void
    {
        $request = new Request();
        $request->headers = new ParameterBag();
        $request->headers->set('x-session-safe-id', 'THIS_IS_THE_SAFE_ID');

        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $this->reqStack->shouldReceive('has')->with('x-session-safe-id')->andReturn(true);
        $this->reqStack->shouldReceive('get')->with('x-session-safe-id')->andReturn('THIS_IS_THE_REQUEST_ID');
        $this->reqStack->shouldReceive('get')->with('x-session-safe-id')->andReturn('THIS_IS_THE_REQUEST_ID');
        $this->container->shouldReceive('has')->with('request_stack')->andReturn(true);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record + ['extra' => ['session_safe_id' => 'THIS_IS_THE_SAFE_ID']], $this->object->processRecord($this->record));
    }

    public function tearDown(): void
    {
        m::close();
    }
}
