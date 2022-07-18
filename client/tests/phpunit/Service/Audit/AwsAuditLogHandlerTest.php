<?php

declare(strict_types=1);

namespace App\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\Result;
use DateTime;
use DateTimeZone;
use Exception;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AwsAuditLogHandlerTest extends TestCase
{
    /** @var AwsAuditLogHandler */
    private $sut;

    /** @var MockObject|CloudWatchLogsClient */
    private $cloudWatchClient;

    public const LOG_GROUP_NAME = 'audit-local';
    public const STREAM_NAME = 'DELETED_CLIENTS';
    private LoggerInterface|MockObject $logger;

    public function setUp(): void
    {
        $this->cloudWatchClient = $this
            ->getMockBuilder(CloudWatchLogsClient::class)
            ->disableOriginalConstructor()
            ->addMethods(['putLogEvents', 'createLogStream', 'describeLogStreams', 'getLogEvents'])
            ->getMock();

        $this->logger = $this
            ->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new AwsAuditLogHandler($this->cloudWatchClient, self::LOG_GROUP_NAME, $this->logger);
    }

//    /**
//     * @test
//     */
//    public function serviceIsInstanceOfAbstractHandler(): void
//    {
//        $this->assertInstanceOf(AbstractProcessingHandler::class, $this->sut);
//    }
//
//    /**
//     * @test
//     *
//     * @throws Exception
//     */
//    public function ignoresRecordsWithoutEventName(): void
//    {
//        $record = [
//            'level' => Logger::NOTICE,
//            'message' => 'Client Deleted',
//            'datetime' => new DateTime('2018-09-02 13:42:23'),
//            'context' => ['type' => 'audit'],
//        ];
//
//        $this
//            ->assertLogStreamWillNotBeCreated()
//            ->assertLogWillNotBePutOnAws();
//
//        $this->sut->handle($record);
//    }
//
//    /**
//     * @test
//     *
//     * @throws Exception
//     */
//    public function ignoresRecordsWithoutEventType(): void
//    {
//        $record = [
//            'level' => Logger::NOTICE,
//            'message' => 'Client Deleted',
//            'datetime' => new DateTime('2018-09-02 13:42:23'),
//            'context' => ['event' => self::STREAM_NAME],
//        ];
//
//        $this
//            ->assertLogStreamWillNotBeCreated()
//            ->assertLogWillNotBePutOnAws();
//
//        $this->sut->handle($record);
//    }
//
//    /**
//     * @test
//     */
//    public function sendsLogMessageWithoutSequenceTokenToNewLogStreamIfStreamDoesNotExistOnAws(): void
//    {
//        $this
//            ->ensureLogStreamWillNotExist()
//            ->assertLogStreamWillBeCreated()
//            ->assertLogWillBePutOnAwsWithoutSequenceToken();
//
//        $this->sut->handle($this->getLogMessageInput());
//    }
//
//    /**
//     * @test
//     */
//    public function sendsLogMessageWithSequenceTokenToExistingLogStreamIfStreamExistsOnAws(): void
//    {
//        $this
//            ->ensureLogStreamWillExist()
//            ->assertLogStreamWillNotBeCreated()
//            ->assertLogWillBePutOnAwsWithSequenceToken();
//
//        $this->sut->handle($this->getLogMessageInput());
//    }
//
//    /**
//     * @test
//     */
//    public function sequenceTokenIsStoredInMemoryForSubsequentWrites(): void
//    {
//        $this
//            ->ensureLogStreamWillExist()
//            ->assertLogStreamWillNotBeCreated()
//            ->assertConsecutiveLogsWillBePutOnAws();
//
//        $this->sut->handle($this->getLogMessageInput());
//        $this->sut->handle($this->getLogMessageInput());
//    }
//
//    /**
//     * @test
//     * @dataProvider awsResultProvider
//     */
//    public function getLogEventsByLogStream(
//        Result $result,
//        string $streamName,
//        int $logStartTime,
//        int $logEndTime
//    ): void {
//        $this
//            ->ensureLogEventsWillExist($result, $streamName, $logStartTime, $logEndTime)
//            ->assertExpectedResultIsReturned($result, $streamName, $logStartTime, $logEndTime);
//    }
//
//    public function awsResultProvider()
//    {
//        return [
//            'one log event' => [
//                new Result([
//                    'events' => [
//                        [
//                            'ingestionTime' => 1643206329732,
//                            'message' => 'something',
//                            'timestamp' => 1643206329733,
//                        ],
//                    ],
//                    'nextBackwardToken' => 'next-sequence-token',
//                    'nextForwardToken' => 'next-sequence-token',
//                ]),
//                'logstream 1',
//                1643206329740,
//                1643206329741,
//            ],
//            'three log events' => [
//                new Result([
//                    'events' => [
//                        [
//                            'ingestionTime' => 1643206329732,
//                            'message' => 'something',
//                            'timestamp' => 1643206329733,
//                        ],
//                        [
//                            'ingestionTime' => 1643206329999,
//                            'message' => 'else',
//                            'timestamp' => 1643206329999,
//                        ],
//                        [
//                            'ingestionTime' => 1643206330000,
//                            'message' => 'returned',
//                            'timestamp' => 1643206330000,
//                        ],
//                    ],
//                    'nextBackwardToken' => 'next-sequence-token',
//                    'nextForwardToken' => 'next-sequence-token',
//                ]),
//                'logstream 2',
//                1643206329750,
//                1643206329751,
//            ],
//        ];
//    }
//
//    private function ensureLogEventsWillExist(
//        Result $result,
//        string $streamName,
//        int $startTime,
//        int $endTime
//    ): self {
//        $this
//            ->cloudWatchClient
//            ->expects($this->once())
//            ->method('getLogEvents')
//            ->with(
//                [
//                    'logGroupName' => self::LOG_GROUP_NAME,
//                    'logStreamName' => $streamName,
//                    'startTime' => $startTime,
//                    'endTime' => $endTime,
//                ]
//            )
//            ->willReturn($result);
//
//        return $this;
//    }
//
//    private function assertExpectedResultIsReturned(
//        Result $expected,
//        string $streamName,
//        int $startTime,
//        int $endTime
//    ): self {
//        $result = $this->sut->getLogEventsByLogStream($streamName, $startTime, $endTime, self::LOG_GROUP_NAME);
//
//        $this->assertEquals($expected, $result);
//
//        return $this;
//    }
//
//    /**
//     * @throws Exception
//     */
//    private function getLogMessageInput(): array
//    {
//        $dateTime = new DateTime('2018-09-02 13:42:23');
//        $timezone = new DateTimeZone(date_default_timezone_get());
//        $dateTime->setTimezone($timezone);
//
//        return [
//            'level' => Logger::NOTICE,
//            'datetime' => $dateTime,
//            'context' => [
//                'event' => self::STREAM_NAME,
//                'type' => 'audit',
//            ],
//        ];
//    }
//
//    private function ensureLogStreamWillExist(): AwsAuditLogHandlerTest
//    {
//        $this
//            ->cloudWatchClient
//            ->method('describeLogStreams')
//            ->willReturn(new Result([
//                'logStreams' => [
//                    [
//                        'logStreamName' => self::STREAM_NAME,
//                        'uploadSequenceToken' => 'next-sequence-token',
//                    ],
//                ],
//            ]));
//
//        return $this;
//    }
//
//    private function ensureLogStreamWillNotExist(): AwsAuditLogHandlerTest
//    {
//        $this
//            ->cloudWatchClient
//            ->method('describeLogStreams')
//            ->willReturn(new Result([
//                'logStreams' => [
//                    [],
//                ],
//            ]));
//
//        return $this;
//    }
//
//    private function assertLogStreamWillBeCreated(): AwsAuditLogHandlerTest
//    {
//        $this
//            ->cloudWatchClient
//            ->expects($this->once())
//            ->method('createLogStream')
//            ->with([
//                'logGroupName' => self::LOG_GROUP_NAME,
//                'logStreamName' => self::STREAM_NAME,
//            ]);
//
//        return $this;
//    }
//
//    private function assertLogStreamWillNotBeCreated(): AwsAuditLogHandlerTest
//    {
//        $this
//            ->cloudWatchClient
//            ->expects($this->never())
//            ->method('createLogStream');
//
//        return $this;
//    }
//
//    /**
//     * @throws Exception
//     */
//    private function assertLogWillBePutOnAwsWithSequenceToken(): void
//    {
//        $this
//            ->cloudWatchClient
//            ->expects($this->once())
//            ->method('putLogEvents')
//            ->with($this->getExpectedMessageWithSequenceToken())
//            ->willReturn(new Result([
//                'nextSequenceToken' => 'next-sequence-token',
//            ]));
//    }
//
//    /**
//     * @throws Exception
//     */
//    private function assertLogWillBePutOnAwsWithoutSequenceToken(): void
//    {
//        $this
//            ->cloudWatchClient
//            ->expects($this->once())
//            ->method('putLogEvents')
//            ->with($this->getExpectedMessageWithoutSequenceToken())
//            ->willReturn(new Result([
//                'nextSequenceToken' => 'next-sequence-token',
//            ]));
//    }
//
//    /**
//     * @throws Exception
//     */
//    private function assertConsecutiveLogsWillBePutOnAws(): void
//    {
//        $this
//            ->cloudWatchClient
//            ->expects($this->exactly(2))
//            ->method('putLogEvents')
//            ->withConsecutive([$this->getExpectedMessageWithSequenceToken()], [$this->getExpectedMessageWithSequenceToken()])
//            ->willReturn(new Result([
//                'nextSequenceToken' => 'next-sequence-token',
//            ]));
//    }
//
//    private function assertLogWillNotBePutOnAws(): void
//    {
//        $this
//            ->cloudWatchClient
//            ->expects($this->never())
//            ->method('putLogEvents');
//    }
//
//    /**
//     * @throws Exception
//     */
//    private function getExpectedMessageWithoutSequenceToken(): array
//    {
//        $dateTime = new DateTime('2018-09-02 13:42:23');
//        $timezone = new DateTimeZone(date_default_timezone_get());
//        $dateTime->setTimezone($timezone);
//
//        $message = [
//            'level' => Logger::NOTICE,
//            'datetime' => $dateTime,
//            'context' => [
//                'event' => self::STREAM_NAME,
//                'type' => 'audit',
//            ],
//        ];
//
//        return [
//            'logGroupName' => self::LOG_GROUP_NAME,
//            'logStreamName' => self::STREAM_NAME,
//            'logEvents' => [
//                [
//                    'message' => json_encode($message, JSON_UNESCAPED_SLASHES)."\n",
//                    'timestamp' => $this->getLogMessageInput()['datetime']->format('U.u') * 1000,
//                ],
//            ],
//        ];
//    }
//
//    /**
//     * @throws Exception
//     */
//    private function getExpectedMessageWithSequenceToken(): array
//    {
//        return $this->getExpectedMessageWithoutSequenceToken() + ['sequenceToken' => 'next-sequence-token'];
//    }
}
