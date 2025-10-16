<?php

namespace App\Service;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class RequestIdLoggerProcessorTest extends TestCase
{
    private array $record = ['key1' => 'abc', 'key2' => 2];
    private Container|MockInterface $container;
    private RequestStack|MockInterface $reqStack;
    private RequestIdLoggerProcessor $object;

    public function setUp(): void
    {
        $this->container = m::mock(Container::class);
        $this->reqStack = m::mock(RequestStack::class);

        $this->object = new RequestIdLoggerProcessor($this->container);
    }

    public function testProcessRecordNoReqStack()
    {
        $this->container->shouldReceive('get')->with('request_stack')->andReturn(null);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequest()
    {
        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn(null);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequestId()
    {
        $request = new Request();
        // No headers set here
        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $result = $this->object->processRecord($this->record);

        $this->assertEquals($this->record, $result);
    }

    public function testProcessRecordHasRequestId()
    {
        $request = new Request();
        $request->headers = new ParameterBag();
        $request->headers->set('x-aws-request-id', 'THIS_IS_THE_REQUEST_ID');

        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $this->reqStack->shouldReceive('has')->with('x-aws-request-id')->andReturn(true);
        $this->reqStack->shouldReceive('get')->with('x-aws-request-id')->andReturn('THIS_IS_THE_REQUEST_ID');
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record + ['extra' => ['aws_request_id' => 'THIS_IS_THE_REQUEST_ID']], $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasRequestIdAndSessionSafeId()
    {
        // Create request and add AWS request ID header
        $request = new Request();
        $request->headers = new ParameterBag();
        $request->headers->set('x-aws-request-id', 'THIS_IS_THE_REQUEST_ID');

        // Create and attach session with session_safe_id
        $session = new Session(new MockArraySessionStorage());
        $session->set('session_safe_id', 'THIS_IS_THE_SESSION_SAFE_ID');
        $request->setSession($session);

        // Mock the request stack and container
        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);
        $this->container->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        // Run
        $result = $this->object->processRecord($this->record);

        // Expected record with both values added
        $expected = $this->record;
        $expected['extra'] = [
            'aws_request_id' => 'THIS_IS_THE_REQUEST_ID',
            'session_safe_id' => 'THIS_IS_THE_SESSION_SAFE_ID',
        ];

        $this->assertEquals($expected, $result);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
