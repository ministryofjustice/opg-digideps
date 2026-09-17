<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Command;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use GuzzleHttp\Exception\TransferException;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\Exception\SlackErrorResponse;
use OPG\Digideps\Frontend\Command\CheckCSVUploadedCommand;
use OPG\Digideps\Frontend\Service\Audit\AwsAuditLogHandler;
use OPG\Digideps\Frontend\Service\Client\GovUK\BankHolidaysAPIClient;
use OPG\Digideps\Frontend\Service\Client\Slack\ClientFactory;
use OPG\Digideps\Frontend\Service\SecretManagerService;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CheckCSVUploadedCommandTest extends KernelTestCase
{
    private const string NO_UPLOAD_MESSAGE_REGEX = '/:cat_spin: The (LAY|ORG) CSV has not been uploaded within the past 24 hours/';

    private BankHolidaysAPIClient&MockObject $bankHolidayAPI;
    private DateTimeProvider&MockObject $dateTimeProvider;
    private AwsAuditLogHandler&MockObject $awsAuditLogHandler;
    private SecretManagerService&MockObject $secretManagerService;
    private ClientFactory&MockObject $slackClientFactory;
    private LoggerInterface&MockObject $logger;
    private \DateTime $now;

    private CommandTester $commandTester;

    private string $auditLogGroupName;
    private string $slackSecret;

    private array $supportedCSVs = [
        CheckCSVUploadedCommand::LAY_CSV,
        CheckCSVUploadedCommand::ORG_CSV,
    ];

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->bankHolidayAPI = self::createMock(BankHolidaysAPIClient::class);
        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);
        $this->awsAuditLogHandler = self::createMock(AwsAuditLogHandler::class);
        $this->secretManagerService = self::createMock(SecretManagerService::class);
        $this->slackClientFactory = self::createMock(ClientFactory::class);
        $this->logger = self::createMock(LoggerInterface::class);
        $this->auditLogGroupName = '/something/logs';

        $sut = new CheckCSVUploadedCommand(
            $this->bankHolidayAPI,
            $this->dateTimeProvider,
            $this->secretManagerService,
            $this->slackClientFactory,
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
        $this->slackSecret = 'AFAKETOKEN';
    }

    public function testExecuteOnNonBankHolidaysWhenAllCSVsHaveBeenUploadedSlackIsNotPostedTo(): void
    {
        $this->todayIsABankHoliday(false);

        $this->aCsvUploadedEventExists(true, [
            CheckCSVUploadedCommand::LAY_CSV,
            CheckCSVUploadedCommand::ORG_CSV,
        ]);

        $this->secretManagerService->expects(self::never())->method('getSecret');
        $this->slackClientFactory->expects(self::never())->method('createClient');

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteOnBankHolidaysSlackIsNotPostedTo(): void
    {
        $this->todayIsABankHoliday(true);

        $this->awsAuditLogHandler->expects(self::never())->method('getLogEventsByLogStream');
        $this->secretManagerService->expects(self::never())->method('getSecret');
        $this->slackClientFactory->expects(self::never())->method('createClient');

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteOnNonBankHolidaysWhenAllCSVsHaveNotBeenUploadedSlackIsPostedTo(): void
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(false);

        $this->secretManagerService->expects(self::atLeastOnce())
            ->method('getSecret')
            ->with('opg-response-slack-token')
            ->willReturn($this->slackSecret);

        $slackClient = self::createMock(Client::class);

        $slackClient->expects(self::exactly(2))
            ->method('chatPostMessage')
            ->willReturnCallback(function (array $params) {
                /** @var string $text */
                $text = $params['text'];

                self::assertEquals('opg-alerts', $params['username']);
                self::assertEquals('opg-digideps-team', $params['channel']);
                self::assertMatchesRegularExpression(self::NO_UPLOAD_MESSAGE_REGEX, $text);
            });

        $this->slackClientFactory->expects(self::atLeast(1))
            ->method('createClient')
            ->with($this->slackSecret)
            ->willReturn($slackClient);

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteOnNonBankHolidaysWhenASiriusLayCSVHasNotBeenUploadedSlackIsPostedTo(): void
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(true, [CheckCSVUploadedCommand::ORG_CSV]);

        $this->secretManagerService->expects(self::once())
            ->method('getSecret')
            ->with('opg-response-slack-token')
            ->willReturn($this->slackSecret);

        $slackClient = self::createMock(Client::class);
        $slackClient->expects(self::once())
            ->method('chatPostMessage')
            ->with([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The LAY CSV has not been uploaded within the past 24 hours',
            ]);

        $this->slackClientFactory->expects(self::once())
            ->method('createClient')
            ->with($this->slackSecret)
            ->willReturn($slackClient);

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteOnNonBankHolidaysWhereLogStreamExistsButNoMatchingCSVEventsExistSlackIsPostedTo(): void
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(true);

        $this->secretManagerService->expects(self::atLeastOnce())
            ->method('getSecret')
            ->with('opg-response-slack-token')
            ->willReturn($this->slackSecret);

        $slackClient = self::createMock(Client::class);
        $slackClient->expects(self::exactly(2))
            ->method('chatPostMessage')
            ->willReturnCallback(function (array $params) {
                /** @var string $text */
                $text = $params['text'];

                self::assertEquals('opg-alerts', $params['username']);
                self::assertEquals('opg-digideps-team', $params['channel']);
                self::assertMatchesRegularExpression(self::NO_UPLOAD_MESSAGE_REGEX, $text);
            });

        $this->slackClientFactory->expects(self::atLeastOnce())
            ->method('createClient')
            ->with($this->slackSecret)
            ->willReturn($slackClient);

        $result = $this->commandTester->execute([]);

        self::assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    public function testExecuteErrorIsLoggedIfCantGetAuditLogs(): void
    {
        $this->todayIsABankHoliday(false);
        $this->cannotRetrieveAuditLogs();

        $this->secretManagerService->expects(self::once())
            ->method('getSecret')
            ->with('opg-response-slack-token')
            ->willReturn($this->slackSecret);

        $slackClient = self::createMock(Client::class);
        $slackClient->expects(self::once())
            ->method('chatPostMessage')
            ->with([
                'username' => 'opg-alerts',
                'channel' => 'opg-digideps-devs',
                'text' => 'Failed to retrieve audit logs during CSV upload check. Error message: The service cannot complete the request.',
            ]);

        $this->slackClientFactory->expects(self::once())
            ->method('createClient')
            ->with($this->slackSecret)
            ->willReturn($slackClient);

        $result = $this->commandTester->execute([]);

        self::assertEquals(1, $result, sprintf('Expected command to return 1, got %d', $result));
    }

    public function testExecuteErrorIsLoggedIfSlackPostIsNotSuccessful(): void
    {
        $this->todayIsABankHoliday(false);
        $this->cannotRetrieveAuditLogs();

        $this->secretManagerService->expects(self::once())
            ->method('getSecret')
            ->with('opg-response-slack-token')
            ->willReturn($this->slackSecret);

        $slackClient = self::createMock(Client::class);

        $slackClient->expects(self::once())
            ->method('chatPostMessage')
            ->willThrowException(new SlackErrorResponse('500', null));

        $this->slackClientFactory->expects(self::once())
            ->method('createClient')
            ->with($this->slackSecret)
            ->willReturn($slackClient);


        $expectations = [
            1 => ['notice', 'Posting CSV upload check to slack'],
            2 => ['error', 'Failed to post to Slack during CSV upload check: Slack returned error code "500"'],
        ];

        $expectedNumInvocations = self::exactly(count($expectations));
        $this->logger->expects($expectedNumInvocations)
            ->method('log')
            ->willReturnCallback(function (string $level, string $message) use ($expectedNumInvocations, $expectations): void {
                $callNum = $expectedNumInvocations->getInvocationCount();
                self::assertEquals($level, $expectations[$callNum][0]);
                self::assertEquals($message, $expectations[$callNum][1]);
            });

        $result = $this->commandTester->execute([]);

        self::assertEquals(1, $result, sprintf('Expected command to return 1, got %d', $result));
    }

    public function testExecuteErrorMessagePostedToSlackWhenUnableToRetrieveBankHolidays(): void
    {
        $this->now = new \DateTime('01-02-2021');

        $this->dateTimeProvider->expects(self::once())
            ->method('getDateTime')
            ->willReturn($this->now);

        $this->bankHolidayAPI->expects(self::once())
            ->method('getBankHolidays')
            ->willThrowException(new TransferException('Something went wrong oops'));

        $this->secretManagerService->expects(self::once())
            ->method('getSecret')
            ->with('opg-response-slack-token')
            ->willReturn($this->slackSecret);

        $slackClient = self::createMock(Client::class);

        $slackClient->expects(self::once())
            ->method('chatPostMessage')
            ->with([
                'username' => 'opg-alerts',
                'channel' => 'opg-digideps-devs',
                'text' => 'Failed to retrieve bank holidays from Gov.uk. Error message: Something went wrong oops',
            ]);

        $this->slackClientFactory->expects(self::once())
            ->method('createClient')
            ->with($this->slackSecret)
            ->willReturn($slackClient);

        $result = $this->commandTester->execute([]);

        self::assertEquals(1, $result, sprintf('Expected command to return 1, got %d', $result));
    }

    private function todayIsABankHoliday(bool $isABankHoliday): void
    {
        $this->bankHolidayAPI->expects(self::once())
            ->method('getBankHolidays')
            ->willReturn(
                [
                'england-and-wales' => [
                    'division' => 'england-and-wales',
                    'events' => [
                        [
                            'title' => 'New Year’s Day',
                            'date' => '2017-01-02',
                            'notes' => 'Substitute day',
                            'bunting' => true,
                        ],
                        [
                            'title' => 'Christmas Day',
                            'date' => '2021-12-27',
                            'notes' => 'Substitute day',
                            'bunting' => false,
                        ],
                    ],
                ],
            ]
            );

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
