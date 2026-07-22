<?php

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use OPG\Digideps\Frontend\Service\RequestIdLoggerProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class RequestIdLoggerProcessorTest extends TestCase
{
    private LogRecord $record;
    private Container&MockObject $container;
    private RequestStack&MockObject $reqStack;
    private RequestIdLoggerProcessor $object;

    public function setUp(): void
    {
        $this->container = $this->createMock(Container::class);
        $this->reqStack = $this->createMock(RequestStack::class);
        $this->record = new LogRecord(new \DateTimeImmutable(), '', Level::Emergency, '', ['key1' => 'abc', 'key2' => 2]);
        $this->object = new RequestIdLoggerProcessor($this->container);
    }

    public function testProcessRecordNoReqStack(): void
    {
        $this->container->method('has')->with('request_stack')->willReturn(false);
        $this->container->method('get')->with('request_stack')->willReturn(null);
        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequest(): void
    {
        $this->reqStack->method('getCurrentRequest')->willReturn(null);
        $this->container->method('get')->with('request_stack')->willReturn($this->reqStack);
        $this->container->method('has')->with('request_stack')->willReturn(false);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequestId(): void
    {
        $request = new Request();
        // No headers set here
        $this->reqStack->method('getCurrentRequest')->willReturn($request);
        $this->container->method('get')->with('request_stack')->willReturn($this->reqStack);
        $this->container->method('has')->with('request_stack')->willReturn(false);

        $result = $this->object->processRecord($this->record);

        $this->assertEquals($this->record, $result);
    }

    public function testProcessRecordHasRequestId(): void
    {
        $request = new Request();
        $request->headers = new ParameterBag();
        $request->headers->set('x-aws-request-id', 'THIS_IS_THE_REQUEST_ID');

        $this->reqStack->method('getCurrentRequest')->willReturn($request);
        $this->container->method('get')->with('request_stack')->willReturn($this->reqStack);
        $this->container->method('has')->with('request_stack')->willReturn(true);

        $record = $this->object->processRecord($this->record);
        $this->assertSame($this->record->context, $record->context);
        $this->assertSame(['aws_request_id' => 'THIS_IS_THE_REQUEST_ID'], $record->extra);
    }

    public function testProcessRecordHasRequestIdAndSessionSafeId(): void
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
        $this->reqStack->method('getCurrentRequest')->willReturn($request);
        $this->container->method('get')->with('request_stack')->willReturn($this->reqStack);
        $this->container->method('has')->with('request_stack')->willReturn(true);

        $record = $this->object->processRecord($this->record);
        $this->assertSame($this->record->context, $record->context);
        // Expected record with both values added
        $this->assertSame([
            'aws_request_id' => 'THIS_IS_THE_REQUEST_ID',
            'session_safe_id' => 'THIS_IS_THE_SESSION_SAFE_ID',
        ], $record->extra);
    }
}
