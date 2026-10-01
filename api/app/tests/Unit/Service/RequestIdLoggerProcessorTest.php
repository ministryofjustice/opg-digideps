<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use Monolog\Level;
use Monolog\LogRecord;
use OPG\Digideps\Backend\Service\RequestIdLoggerProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestIdLoggerProcessorTest extends TestCase
{
    private Container&MockObject $container;
    private RequestStack&MockObject $reqStack;
    private LogRecord $record;
    private RequestIdLoggerProcessor $sut;

    public function setUp(): void
    {
        $this->container = self::createMock(Container::class);
        $this->reqStack = self::createMock(RequestStack::class);
        $this->record = new LogRecord(new \DateTimeImmutable(), '', Level::Emergency, '', ['key1' => 'abc', 'key2' => 2]);
        $this->sut = new RequestIdLoggerProcessor($this->container);
    }

    public function testProcessRecordNoReqStack(): void
    {
        $this->container->expects(self::once())->method('has')->with('request_stack')->willReturn(false);
        $this->assertEquals($this->record, $this->sut->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequest(): void
    {
        $this->reqStack->expects(self::once())->method('getCurrentRequest')->willReturn(null);
        $this->container->expects(self::once())->method('has')->with('request_stack')->willReturn(true);
        $this->container->expects(self::once())->method('get')->with('request_stack')->willReturn($this->reqStack);

        $this->assertEquals($this->record, $this->sut->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequestId(): void
    {
        $request = new Request();
        $request->headers = new ParameterBag(['x-request-id' => 'abc']);

        $this->reqStack->expects(self::once())->method('getCurrentRequest')->willReturn($request);
        $this->container->expects(self::once())->method('has')->with('request_stack')->willReturn(true);
        $this->container->expects(self::once())->method('get')->with('request_stack')->willReturn($this->reqStack);

        $this->assertEquals($this->record, $this->sut->processRecord($this->record));
    }

    public function testProcessRecordHasRequestId(): void
    {
        $request = new Request();
        $request->headers = new HeaderBag(['x-aws-request-id' => 'THIS_IS_THE_REQUEST_ID']);

        $this->reqStack->expects(self::once())->method('getCurrentRequest')->willReturn($request);
        $this->container->expects(self::once())->method('has')->with('request_stack')->willReturn(true);
        $this->container->expects(self::once())->method('get')->with('request_stack')->willReturn($this->reqStack);

        $record = $this->sut->processRecord($this->record);
        $this->assertSame($this->record->context, $record->context);
        $this->assertSame(['aws_request_id' => 'THIS_IS_THE_REQUEST_ID'], $record->extra);
    }

    public function testProcessRecordHasSafeId(): void
    {
        $request = new Request();
        $request->headers = new HeaderBag(['x-session-safe-id' => 'THIS_IS_THE_SAFE_ID']);

        $this->reqStack->expects(self::once())->method('getCurrentRequest')->willReturn($request);
        $this->container->expects(self::once())->method('has')->with('request_stack')->willReturn(true);
        $this->container->expects(self::once())->method('get')->with('request_stack')->willReturn($this->reqStack);

        $record = $this->sut->processRecord($this->record);
        $this->assertSame($this->record->context, $record->context);
        $this->assertSame(['session_safe_id' => 'THIS_IS_THE_SAFE_ID'], $record->extra);
    }

    public function testProcessRecordHasNullSafeId(): void
    {
        $request = new Request();
        $request->headers = new HeaderBag(['x-session-safe-id' => null]);

        $this->reqStack->expects(self::once())->method('getCurrentRequest')->willReturn($request);
        $this->container->expects(self::once())->method('has')->with('request_stack')->willReturn(true);
        $this->container->expects(self::once())->method('get')->with('request_stack')->willReturn($this->reqStack);

        $this->assertEquals($this->record, $this->sut->processRecord($this->record));
    }
}
