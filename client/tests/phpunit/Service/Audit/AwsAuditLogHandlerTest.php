<?php declare(strict_types=1);

namespace AppBundle\Service\Audit;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\Result;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AwsAuditLogHandlerTest extends TestCase
{
    /** @var AwsAuditLogHandler */
    private $sut;

    /** @var MockObject | CloudWatchLogsClient */
    private $cloudWatchClient;

    const LOG_GROUP_NAME = 'audit-local';
    const STREAM_NAME = 'DELETED_CLIENTS';

    public function setUp(): void
    {
        $this->cloudWatchClient = $this
            ->getMockBuilder(CloudWatchLogsClient::class)
            ->disableOriginalConstructor()
            ->addMethods(['putLogEvents', 'createLogStream', 'describeLogStreams', 'createLogGroup', 'describeLogGroups'])
            ->getMock();

        $this->sut = new AwsAuditLogHandler($this->cloudWatchClient, self::LOG_GROUP_NAME);
    }

    /**
     * @test
     */
    public function serviceIsInstanceOfAbstractHandler(): void
    {
        $this->assertInstanceOf(AbstractProcessingHandler::class, $this->sut);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function ignoresRecordsWithoutEventName(): void
    {
        $record = [
            'level' => Logger::NOTICE,
            'message' => "Client Deleted",
            'datetime' => new \DateTime('2018-09-02 13:42:23'),
            'context' => ['type' => 'audit']
        ];

        $this
            ->assertLogGroupWillNotBeCreated()
            ->assertLogStreamWillNotBeCreated()
            ->assertLogWillNotBePutOnAws();

        $this->sut->handle($record);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function ignoresRecordsWithoutEventType(): void
    {
        $record = [
            'level' => Logger::NOTICE,
            'message' => "Client Deleted",
            'datetime' => new \DateTime('2018-09-02 13:42:23'),
            'context' => ['event' => self::STREAM_NAME]
        ];

        $this
            ->assertLogGroupWillNotBeCreated()
            ->assertLogStreamWillNotBeCreated()
            ->assertLogWillNotBePutOnAws();

        $this->sut->handle($record);
    }

    /**
     * @test
     */
    public function sendsLogMessageWithoutSequenceTokenToNewLogStreamIfStreamDoesNotExistOnAws(): void
    {
        $this
            ->ensureLogGroupWillExist()
            ->assertLogGroupWillNotBeCreated()
            ->ensureLogStreamWillNotExist()
            ->assertLogStreamWillBeCreated()
            ->assertLogWillBePutOnAwsWithoutSequenceToken();

        $this->sut->handle($this->getLogMessageInput());
    }

    /**
     * @test
     */
    public function sendsLogMessageWithSequenceTokenToExistingLogStreamIfStreamExistsOnAws(): void
    {
        $this
            ->ensureLogGroupWillExist()
            ->assertLogGroupWillNotBeCreated()
            ->ensureLogStreamWillExist()
            ->assertLogStreamWillNotBeCreated()
            ->assertLogWillBePutOnAwsWithSequenceToken();

        $this->sut->handle($this->getLogMessageInput());
    }

    /**
     * @test
     */
    public function sequenceTokenIsStoredInMemoryForSubsequentWrites(): void
    {
        $this
            ->ensureLogGroupWillExist()
            ->assertLogGroupWillNotBeCreated()
            ->ensureLogStreamWillExist()
            ->assertLogStreamWillNotBeCreated()
            ->assertConsecutiveLogsWillBePutOnAws();

        $this->sut->handle($this->getLogMessageInput());
        $this->sut->handle($this->getLogMessageInput());
    }

    /**
     * @test
     */
    public function createsLogGroupInAwsIfNotAlreadyCreated(): void
    {
        $this
            ->ensureLogGroupWillNotExist()
            ->assertLogGroupWillBeCreated()
            ->ensureLogStreamWillExist()
            ->assertLogWillBePutOnAwsWithSequenceToken();

        $this->sut->handle($this->getLogMessageInput());
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getLogMessageInput(): array
    {
        $dateTime = new \DateTime('2018-09-02 13:42:23');
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $dateTime->setTimezone($timezone);

        return [
            'level' => Logger::NOTICE,
            'datetime' => $dateTime,
            'context' => [
                'event' => self::STREAM_NAME,
                'type' => 'audit'
            ]
        ];
    }

    /**
     * @return AwsAuditLogHandlerTest
     */
    private function ensureLogGroupWillExist(): AwsAuditLogHandlerTest
    {
        $this
            ->cloudWatchClient
            ->expects($this->once())
            ->method('describeLogGroups')
            ->with(['logGroupNamePrefix' => self::LOG_GROUP_NAME])
            ->willReturn(new Result([
                'logGroups' => [
                    [
                        'logGroupName' => self::LOG_GROUP_NAME
                    ]
                ]
            ]));

        return $this;
    }

    /**
     * @return AwsAuditLogHandlerTest
     */
    private function ensureLogGroupWillNotExist(): AwsAuditLogHandlerTest
    {
        $this
            ->cloudWatchClient
            ->method('describeLogGroups')
            ->with(['logGroupNamePrefix' => self::LOG_GROUP_NAME])
            ->willReturn(new Result([
                'logGroups' => [
                    []
                ]
            ]));

        return $this;
    }

    /**
     * @return AwsAuditLogHandlerTest
     */
    private function assertLogGroupWillBeCreated(): AwsAuditLogHandlerTest
    {
        $this
            ->cloudWatchClient
            ->expects($this->once())
            ->method('createLogGroup')
            ->with(['logGroupName' => self::LOG_GROUP_NAME]);

        return $this;
    }

    /**
     * @return AwsAuditLogHandlerTest
     */
    private function assertLogGroupWillNotBeCreated(): AwsAuditLogHandlerTest
    {
        $this
            ->cloudWatchClient
            ->expects($this->never())
            ->method('createLogGroup');

        return $this;
    }

    /**
     * @return AwsAuditLogHandlerTest
     */
    private function ensureLogStreamWillExist(): AwsAuditLogHandlerTest
    {
        $this
            ->cloudWatchClient
            ->method('describeLogStreams')
            ->willReturn(new Result([
                'logStreams' => [
                    [
                        'logStreamName' => self::STREAM_NAME,
                        'uploadSequenceToken' => 'next-sequence-token'
                    ]
                ]
            ]));

        return $this;
    }

    /**
     * @return AwsAuditLogHandlerTest
     */
    private function ensureLogStreamWillNotExist(): AwsAuditLogHandlerTest
    {
        $this
            ->cloudWatchClient
            ->method('describeLogStreams')
            ->willReturn(new Result([
                'logStreams' => [
                    []
                ]
            ]));

        return $this;
    }

    /**
     * @return AwsAuditLogHandlerTest
     */
    private function assertLogStreamWillBeCreated(): AwsAuditLogHandlerTest
    {
        $this
            ->cloudWatchClient
            ->expects($this->once())
            ->method('createLogStream')
            ->with([
                'logGroupName' => self::LOG_GROUP_NAME,
                'logStreamName' => self::STREAM_NAME
            ]);

        return $this;
    }

    /**
     * @return AwsAuditLogHandlerTest
     */
    private function assertLogStreamWillNotBeCreated(): AwsAuditLogHandlerTest
    {
        $this
            ->cloudWatchClient
            ->expects($this->never())
            ->method('createLogStream');

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function assertLogWillBePutOnAwsWithSequenceToken(): void
    {
        $this
            ->cloudWatchClient
            ->expects($this->once())
            ->method('putLogEvents')
            ->with($this->getExpectedMessageWithSequenceToken())
            ->willReturn(new Result([
                'nextSequenceToken' => 'next-sequence-token'
            ]));
    }

    /**
     * @throws \Exception
     */
    private function assertLogWillBePutOnAwsWithoutSequenceToken(): void
    {
        $this
            ->cloudWatchClient
            ->expects($this->once())
            ->method('putLogEvents')
            ->with($this->getExpectedMessageWithoutSequenceToken())
            ->willReturn(new Result([
                'nextSequenceToken' => 'next-sequence-token'
            ]));
    }

    /**
     * @throws \Exception
     */
    private function assertConsecutiveLogsWillBePutOnAws(): void
    {
        $this
            ->cloudWatchClient
            ->expects($this->exactly(2))
            ->method('putLogEvents')
            ->withConsecutive([$this->getExpectedMessageWithSequenceToken()], [$this->getExpectedMessageWithSequenceToken()])
            ->willReturn(new Result([
                'nextSequenceToken' => 'next-sequence-token'
            ]));
    }

    /**
     *
     */
    private function assertLogWillNotBePutOnAws(): void
    {
        $this
            ->cloudWatchClient
            ->expects($this->never())
            ->method('putLogEvents');
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getExpectedMessageWithoutSequenceToken(): array
    {
        $dateTime = new \DateTime('2018-09-02 13:42:23');
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $dateTime->setTimezone($timezone);

        $message =  [
            'level' => Logger::NOTICE,
            'datetime' => $dateTime,
            'context' => [
                'event' => self::STREAM_NAME,
                'type' => 'audit'
            ]
        ];

        return [
            'logGroupName' => self::LOG_GROUP_NAME,
            'logStreamName' => self::STREAM_NAME,
            'logEvents' => [
                [
                    'message' => json_encode($message, JSON_UNESCAPED_SLASHES) . "\n",
                    'timestamp' => $this->getLogMessageInput()['datetime']->format('U.u') * 1000
                ]
            ]
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getExpectedMessageWithSequenceToken(): array
    {
        return $this->getExpectedMessageWithoutSequenceToken() + ['sequenceToken' => 'next-sequence-token'];
    }
}
