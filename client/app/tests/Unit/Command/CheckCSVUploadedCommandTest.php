<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Command;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use OPG\Digideps\Frontend\Command\CheckCSVUploadedCommand;
use OPG\Digideps\Frontend\Service\Audit\AwsAuditLogHandler;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CheckCSVUploadedCommandTest extends KernelTestCase
{
    private const string NO_UPLOAD_MESSAGE_REGEX = '/check_csv_uploaded - failure - The (LAY|ORG) CSV has not been uploaded within the past 24 hours/';

    private DateTimeProvider&MockObject $dateTimeProvider;
    private AwsAuditLogHandler&MockObject $awsAuditLogHandler;
    private LoggerInterface&MockObject $logger;
    private \DateTime $now;

    private CommandTester $commandTester;

    private string $auditLogGroupName;

    private array $supportedCSVs = [
        CheckCSVUploadedCommand::LAY_CSV,
        CheckCSVUploadedCommand::ORG_CSV,
    ];

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);
        $this->awsAuditLogHandler = self::createMock(AwsAuditLogHandler::class);
        $this->logger = self::createMock(LoggerInterface::class);
        $this->auditLogGroupName = '/something/logs';

        $sut = new CheckCSVUploadedCommand(
            $this->dateTimeProvider,
            $this->awsAuditLogHandler,
            $this->logger,
            $this->auditLogGroupName
        );

        $app->add($sut);

        $commandName = CheckCSVUploadedCommand::$defaultName;
        self::assertIsString($commandName);

        $command = $app->find($commandName);
        $this->commandTester = new CommandTester($command);

        $this->now = new \DateTime();
    }

    public function testExecuteOnNonBankHolidaysWhenAllCSVsHaveBeenUploadedSlackIsNotPostedTo(): void
    {
        $this->todayIsABankHoliday(false);

        $this->aCsvUploadedEventExists(true, [
            CheckCSVUploadedCommand::LAY_CSV,
            CheckCSVUploadedCommand::ORG_CSV,
        ]);

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteOnBankHolidaysSlackIsNotPostedTo(): void
    {
        $this->todayIsABankHoliday(true);

        $this->awsAuditLogHandler->expects(self::never())->method('getLogEventsByLogStream');

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteOnNonBankHolidaysWhenAllCSVsHaveNotBeenUploadedSlackIsPostedTo(): void
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(false);

        $this->logger->expects(self::exactly(2))
            ->method('error')
            ->with(new RegularExpression(self::NO_UPLOAD_MESSAGE_REGEX));

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteOnNonBankHolidaysWhenASiriusLayCSVHasNotBeenUploadedSlackIsPostedTo(): void
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(true, [CheckCSVUploadedCommand::ORG_CSV]);

        $this->logger->expects(self::once())
            ->method('error')
            ->with('check_csv_uploaded - failure - The LAY CSV has not been uploaded within the past 24 hours');

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteOnNonBankHolidaysWhereLogStreamExistsButNoMatchingCSVEventsExistSlackIsPostedTo(): void
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(true);

        $this->logger->expects(self::exactly(2))
            ->method('error')
            ->with(new RegularExpression(self::NO_UPLOAD_MESSAGE_REGEX));

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteErrorIsLoggedIfCantGetAuditLogs(): void
    {
        $this->todayIsABankHoliday(false);
        $this->cannotRetrieveAuditLogs();

        $this->logger->expects(self::once())
            ->method('warning')
            ->with('check_csv_uploaded - failure - Failed to retrieve audit logs during CSV upload check. Error message: The service cannot complete the request.');

        $result = $this->commandTester->execute([]);

        self::assertEquals(1, $result, sprintf('Expected command to return 1, got %d', $result));
    }

    private function todayIsABankHoliday(bool $isABankHoliday): void
    {
        $this->now = new \DateTime($isABankHoliday ? '27-12-2021' : '01-02-2021');
        $this->dateTimeProvider->expects(self::once())
            ->method('getDateTime')
            ->willReturn($this->now);
    }

    private function aCsvUploadedEventExists(bool $exists, array $uploadedCSVs = []): void
    {
        $startingTime = (int) (clone $this->now)->sub(new \DateInterval('P1D'))->format('Uv');
        $endTime = (int) (clone $this->now)->format('Uv');

        if ($exists) {
            $this->awsAuditLogHandler->expects(self::once())
                ->method('getLogStreams')
                ->with($this->auditLogGroupName)
                ->willReturn(new Result(
                    [
                        'logStreams' => [
                            [
                                'logStreamName' => 'CSV_UPLOADED',
                                'creationTime' => 1649928424320,
                                'firstEventTimestamp' => 1649928424261.6438,
                                'lastEventTimestamp' => 1649928531011.631,
                                'lastIngestionTime' => 1649928531058,
                                'uploadSequenceToken' => '2',
                                'arn' => 'arn:aws:logs:eu-west-1:000000000000:log-group:audit-local:log-stream:CSV_UPLOADED',
                                'storedBytes' => 645,
                            ],
                        ],
                    ]
                ));

            $events = $this->populateLogEvents($uploadedCSVs);
            $expectedResponseFromAWS = new Result(
                [
                    'events' => $events,
                    'nextBackwardToken' => 'next-sequence-token',
                    'nextForwardToken' => 'next-sequence-token',
                ]
            );

            $this->awsAuditLogHandler->expects(self::once())
                ->method('getLogEventsByLogStream')
                ->with(
                    'CSV_UPLOADED',
                    $startingTime,
                    $endTime,
                    $this->auditLogGroupName
                )
                ->willReturn($expectedResponseFromAWS);
        } else {
            $this->awsAuditLogHandler->expects(self::once())
                ->method('getLogStreams')
                ->with($this->auditLogGroupName)
                ->willReturn([]);
        }
    }

    // Creates a Result object based on the CSV types passed in
    private function populateLogEvents(array $uploadedCSVs): array
    {
        $events = [];

        if (!empty($uploadedCSVs)) {
            foreach ($uploadedCSVs as $csvType) {
                if (in_array($csvType, $this->supportedCSVs)) {
                    $events[] = [
                        'ingestionTime' => 1643206329732,
                        'message' => sprintf(
                            '{"message":"","context":{"trigger":"CSV_UPLOADED",,"role_type":"%s","}',
                            strtoupper($csvType)
                        ),
                        'timestamp' => 1643206329733,
                    ];
                }
            }
        } else {
            $events = [
                'ingestionTime' => 1643206329732,
                'message' => 'something',
                'timestamp' => 1643206329733,
            ];
        }

        return $events;
    }

    private function cannotRetrieveAuditLogs(): void
    {
        $startingTime = (int) (clone $this->now)->sub(new \DateInterval('P1D'))->format('Uv');
        $endTime = (int) (clone $this->now)->format('Uv');

        $this->awsAuditLogHandler->expects(self::once())
            ->method('getLogStreams')
            ->with($this->auditLogGroupName)
            ->willReturn(new Result(
                [
                    'logStreams' => [
                        [
                            'logStreamName' => 'CSV_UPLOADED',
                            'creationTime' => 1649928424320,
                            'firstEventTimestamp' => 1649928424261.6438,
                            'lastEventTimestamp' => 1649928531011.631,
                            'lastIngestionTime' => 1649928531058,
                            'uploadSequenceToken' => '2',
                            'arn' => 'arn:aws:logs:eu-west-1:000000000000:log-group:audit-local:log-stream:CSV_UPLOADED',
                            'storedBytes' => 645,
                        ],
                    ],
                ]
            ));

        $exception = new AwsException(
            'The service cannot complete the request.',
            new Command('getLogEvents'),
            ['code' => 503]
        );

        $this->awsAuditLogHandler->expects(self::once())
            ->method('getLogEventsByLogStream')
            ->with(
                'CSV_UPLOADED',
                $startingTime,
                $endTime,
                $this->auditLogGroupName
            )
            ->willThrowException($exception);
    }
}
