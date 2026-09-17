<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\Result;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use OPG\Digideps\Frontend\Service\Audit\AwsAuditLogHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AwsAuditLogHandlerTest extends TestCase
{
    private MockObject&CloudWatchLogsClient $cloudWatchClient;
    public const string LOG_GROUP_NAME = 'audit-local';
    public const string STREAM_NAME = 'DELETED_CLIENTS';
    private AwsAuditLogHandler $sut;

    public function setUp(): void
    {
        $this->cloudWatchClient = $this->getMockBuilder(CloudWatchLogsClient::class)
            ->disableOriginalConstructor()
            ->addMethods(['putLogEvents', 'createLogStream', 'describeLogStreams', 'getLogEvents'])
            ->getMock();

        $this->sut = new AwsAuditLogHandler($this->cloudWatchClient, self::LOG_GROUP_NAME);
    }

    public function testIgnoresRecordsWithoutEventName(): void
    {
        $record = new LogRecord(
            new \DateTimeImmutable('2018-09-02 13:42:23'),
            'client',
            Level::Notice,
            'Client Deleted',
            ['type' => 'audit'],
        );

        $this->assertLogStreamWillNotBeCreated()
            ->assertLogWillNotBePutOnAws();

        $this->sut->handle($record);
    }

    public function testIgnoresRecordsWithoutEventType(): void
    {
        $record = new LogRecord(
            new \DateTimeImmutable('2018-09-02 13:42:23'),
            'client',
            Level::Notice,
            'Client Deleted',
            ['event' => self::STREAM_NAME],
        );

        $this->assertLogStreamWillNotBeCreated()
            ->assertLogWillNotBePutOnAws();

        $this->sut->handle($record);
    }

    public function testSendsLogMessageWithoutSequenceTokenToNewLogStreamIfStreamDoesNotExistOnAws(): void
    {
        $this->ensureLogStreamWillNotExist()
            ->assertLogStreamWillBeCreated()
            ->assertLogWillBePutOnAwsWithoutSequenceToken();

        $this->sut->handle($this->getLogMessageInput());
    }

    public function testSendsLogMessageWithSequenceTokenToExistingLogStreamIfStreamExistsOnAws(): void
    {
        $this->ensureLogStreamWillExist()
            ->assertLogStreamWillNotBeCreated()
            ->assertLogWillBePutOnAwsWithSequenceToken();

        $this->sut->handle($this->getLogMessageInput());
    }

    public function testSequenceTokenIsStoredInMemoryForSubsequentWrites(): void
    {
        $this->ensureLogStreamWillExist()
            ->assertLogStreamWillNotBeCreated()
            ->assertConsecutiveLogsWillBePutOnAws();

        $this->sut->handle($this->getLogMessageInput());
        $this->sut->handle($this->getLogMessageInput());
    }

    /**
     * @dataProvider awsResultProvider
     */
    public function testGetLogEventsByLogStream(
        Result $result,
        string $streamName,
        int $logStartTime,
        int $logEndTime
    ): void {
        $this
            ->ensureLogEventsWillExist($result, $streamName, $logStartTime, $logEndTime)
            ->assertExpectedResultIsReturned($result, $streamName, $logStartTime, $logEndTime);
    }

    public static function awsResultProvider(): array
    {
        return [
            'one log event' => [
                new Result([
                    'events' => [
                        [
                            'ingestionTime' => 1643206329732,
                            'message' => 'something',
                            'timestamp' => 1643206329733,
                        ],
                    ],
                    'nextBackwardToken' => 'next-sequence-token',
                    'nextForwardToken' => 'next-sequence-token',
                ]),
                'logstream 1',
                1643206329740,
                1643206329741,
            ],
            'three log events' => [
                new Result([
                    'events' => [
                        [
                            'ingestionTime' => 1643206329732,
                            'message' => 'something',
                            'timestamp' => 1643206329733,
                        ],
                        [
                            'ingestionTime' => 1643206329999,
                            'message' => 'else',
                            'timestamp' => 1643206329999,
                        ],
                        [
                            'ingestionTime' => 1643206330000,
                            'message' => 'returned',
                            'timestamp' => 1643206330000,
                        ],
                    ],
                    'nextBackwardToken' => 'next-sequence-token',
                    'nextForwardToken' => 'next-sequence-token',
                ]),
                'logstream 2',
                1643206329750,
                1643206329751,
            ],
        ];
    }

    private function ensureLogEventsWillExist(
        Result $result,
        string $streamName,
        int $startTime,
        int $endTime
    ): static {
        $this
            ->cloudWatchClient
            ->expects($this->once())
            ->method('getLogEvents')
            ->with(
                [
                    'logGroupName' => self::LOG_GROUP_NAME,
                    'logStreamName' => $streamName,
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                ]
            )
            ->willReturn($result);

        return $this;
    }

    private function assertExpectedResultIsReturned(
        Result $expected,
        string $streamName,
        int $startTime,
        int $endTime
    ): void {
        $result = $this->sut->getLogEventsByLogStream($streamName, $startTime, $endTime, self::LOG_GROUP_NAME);

        $this->assertEquals($expected, $result);

    }

    /**
     * @throws \Exception
     */
    private function getLogMessageInput(): LogRecord
    {
        $dateTime = new \DateTimeImmutable('2018-09-02 13:42:23');
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $dateTime = $dateTime->setTimezone($timezone);

        return new LogRecord($dateTime, 'client', Level::Notice, '', [
            'event' => self::STREAM_NAME,
            'type' => 'audit',
        ]);
    }

    private function ensureLogStreamWillExist(): AwsAuditLogHandlerTest
    {
        $this->cloudWatchClient->expects(self::atLeastOnce())
            ->method('describeLogStreams')
            ->willReturn(new Result([
                'logStreams' => [
                    [
                        'logStreamName' => self::STREAM_NAME,
                        'uploadSequenceToken' => 'next-sequence-token',
                    ],
                ],
            ]));

        return $this;
    }

    private function ensureLogStreamWillNotExist(): AwsAuditLogHandlerTest
    {
        $this->cloudWatchClient->expects(self::once())
            ->method('describeLogStreams')
            ->willReturn(new Result([
                'logStreams' => [
                    [],
                ],
            ]));

        return $this;
    }

    private function assertLogStreamWillBeCreated(): AwsAuditLogHandlerTest
    {
        $this->cloudWatchClient->expects(self::once())
            ->method('createLogStream')
            ->with([
                'logGroupName' => self::LOG_GROUP_NAME,
                'logStreamName' => self::STREAM_NAME,
            ]);

        return $this;
    }

    private function assertLogStreamWillNotBeCreated(): AwsAuditLogHandlerTest
    {
        $this->cloudWatchClient->expects(self::never())->method('createLogStream');

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function assertLogWillBePutOnAwsWithSequenceToken(): void
    {
        $this->cloudWatchClient->expects(self::once())
            ->method('putLogEvents')
            ->with($this->getExpectedMessageWithSequenceToken())
            ->willReturn(new Result([
                'nextSequenceToken' => 'next-sequence-token',
            ]));
    }

    /**
     * @throws \Exception
     */
    private function assertLogWillBePutOnAwsWithoutSequenceToken(): void
    {
        $this->cloudWatchClient->expects(self::once())
            ->method('putLogEvents')
            ->with($this->getExpectedMessageWithoutSequenceToken())
            ->willReturn(new Result([
                'nextSequenceToken' => 'next-sequence-token',
            ]));
    }

    /**
     * @throws \Exception
     */
    private function assertConsecutiveLogsWillBePutOnAws(): void
    {
        $expectedTokens = [
            1 => $this->getExpectedMessageWithSequenceToken(),
            2 => $this->getExpectedMessageWithSequenceToken(),
        ];

        $invocationMatcher = self::exactly(count($expectedTokens));

        $this->cloudWatchClient->expects($invocationMatcher)
            ->method('putLogEvents')
            ->willReturnCallback(function (array $tokens) use ($expectedTokens, $invocationMatcher) {
                $invocation = $invocationMatcher->getInvocationCount();

                self::assertEquals($expectedTokens[$invocation], $tokens);

                return new Result(['nextSequenceToken' => 'next-sequence-token']);
            });
    }

    private function assertLogWillNotBePutOnAws(): void
    {
        $this->cloudWatchClient->expects($this->never())->method('putLogEvents');
    }

    /**
     * @throws \Exception
     */
    private function getExpectedMessageWithoutSequenceToken(): array
    {
        $dateTime = new \DateTime('2018-09-02 13:42:23');
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $dateTime->setTimezone($timezone);

        $message = [
            'level' => Logger::NOTICE,
            'datetime' => $dateTime,
            'context' => [
                'event' => self::STREAM_NAME,
                'type' => 'audit',
            ],
        ];

        return [
            'logGroupName' => self::LOG_GROUP_NAME,
            'logStreamName' => self::STREAM_NAME,
            'logEvents' => [
                [
                    'message' => json_encode($message, JSON_UNESCAPED_SLASHES) . "\n",
                    'timestamp' => $this->getLogMessageInput()['datetime']->format('U.u') * 1000,
                ],
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    private function getExpectedMessageWithSequenceToken(): array
    {
        return $this->getExpectedMessageWithoutSequenceToken() + ['sequenceToken' => 'next-sequence-token'];
    }
}
