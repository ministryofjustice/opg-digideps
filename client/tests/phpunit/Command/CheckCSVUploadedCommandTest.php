<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CheckCSVUploadedCommand;
use App\Service\Audit\AwsAuditLogHandler;
use App\Service\Client\GovUK\BankHolidaysAPIClient;
use App\Service\Client\Slack\ClientFactory;
use App\Service\SecretManagerService;
use App\Service\Time\DateTimeProvider;
use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use DateInterval;
use DateTime;
use GuzzleHttp\Exception\TransferException;
use JoliCode\Slack\Api\Client;
use JoliCode\Slack\Exception\SlackErrorResponse;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CheckCSVUploadedCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    private ObjectProphecy | BankHolidaysAPIClient $bankHolidayAPI;
    private ObjectProphecy | DateTimeProvider $dateTimeProvider;
    private ObjectProphecy | AwsAuditLogHandler $awsAuditLogHandler;
    private ObjectProphecy | SecretManagerService $secretManagerService;
    private ObjectProphecy | ClientFactory $slackClientFactory;
    private ObjectProphecy | LoggerInterface $logger;

    private CommandTester $commandTester;

    private DateTime $now;

    private string $auditLogGroupName;
    private string $slackSecret;

    private array $supportedCSVs = [
        CheckCSVUploadedCommand::CASREC_LAY_CSV,
        CheckCSVUploadedCommand::SIRIUS_LAY_CSV,
        CheckCSVUploadedCommand::CASREC_PROF_CSV,
        CheckCSVUploadedCommand::CASREC_PA_CSV,
    ];

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->bankHolidayAPI = self::prophesize(BankHolidaysAPIClient::class);
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->awsAuditLogHandler = self::prophesize(AwsAuditLogHandler::class);
        $this->secretManagerService = self::prophesize(SecretManagerService::class);
        $this->slackClientFactory = self::prophesize(ClientFactory::class);
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->auditLogGroupName = '/something/logs';

        $sut = new CheckCSVUploadedCommand(
            $this->bankHolidayAPI->reveal(),
            $this->dateTimeProvider->reveal(),
            $this->secretManagerService->reveal(),
            $this->slackClientFactory->reveal(),
            $this->awsAuditLogHandler->reveal(),
            $this->logger->reveal(),
            $this->auditLogGroupName
        );

        $app->add($sut);

        $command = $app->find(CheckCSVUploadedCommand::$defaultName);
        $this->commandTester = new CommandTester($command);

        $this->now = new DateTime();
        $this->slackSecret = 'AFAKETOKEN';
    }

    /**
     * @test
     */
    public function executeOnNonBankHolidaysWhenAllCSVsHaveBeenUploadedSlackIsNotPostedTo()
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(true, [
            CheckCSVUploadedCommand::CASREC_LAY_CSV,
            CheckCSVUploadedCommand::SIRIUS_LAY_CSV,
            CheckCSVUploadedCommand::CASREC_PROF_CSV,
            CheckCSVUploadedCommand::CASREC_PA_CSV,
        ]);

        $this->secretManagerService->getSecret(Argument::any())->shouldNotBeCalled();
        $this->slackClientFactory->createClient(Argument::any())->shouldNotBeCalled();
        $slackClient = self::prophesize(Client::class);
        $slackClient->chatPostMessage(Argument::any())->shouldNotBeCalled();

        $result = $this->commandTester->execute([]);

        $this->assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    /**
     * @test
     */
    public function executeOnBankHolidaysSlackIsNotPostedTo()
    {
        $this->todayIsABankHoliday(true);

        $this->awsAuditLogHandler->getLogEventsByLogStream(Argument::cetera())->shouldNotBeCalled();
        $this->secretManagerService->getSecret(Argument::any())->shouldNotBeCalled();
        $this->slackClientFactory->createClient(Argument::any())->shouldNotBeCalled();
        $slackClient = self::prophesize(Client::class);
        $slackClient->chatPostMessage(Argument::any())->shouldNotBeCalled();

        $result = $this->commandTester->execute([]);

        $this->assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    /**
     * @test
     */
    public function executeOnNonBankHolidaysWhenAllCSVsHaveNotBeenUploadedSlackIsPostedTo()
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(false);

        $this->secretManagerService->getSecret('opg-alerts-slack-token')
            ->shouldBeCalled()
            ->willReturn($this->slackSecret);

        $slackClient = self::prophesize(Client::class);
        $slackClient->chatPostMessage([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The CasRec Lay CSV has not been uploaded within the past 24 hours',
            ])
            ->shouldBeCalled();
        $slackClient->chatPostMessage([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The Sirius Lay CSV has not been uploaded within the past 24 hours',
            ])
            ->shouldBeCalled();
        $slackClient->chatPostMessage([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The CasRec Prof CSV has not been uploaded within the past 24 hours',
            ])
            ->shouldBeCalled();
        $slackClient->chatPostMessage([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The CasRec PA CSV has not been uploaded within the past 24 hours',
            ])
            ->shouldBeCalled();

        $this->slackClientFactory->createClient($this->slackSecret)
            ->shouldBeCalled()
            ->willReturn($slackClient->reveal());

        $result = $this->commandTester->execute([]);

        $this->assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    /**
     * @test
     */
    public function executeOnNonBankHolidaysWhenACasRecLayCSVHaveNotBeenUploadedSlackIsPostedTo()
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(true, [
            CheckCSVUploadedCommand::SIRIUS_LAY_CSV,
            CheckCSVUploadedCommand::CASREC_PROF_CSV,
            CheckCSVUploadedCommand::CASREC_PA_CSV,
        ]);

        $this->secretManagerService->getSecret('opg-alerts-slack-token')
            ->shouldBeCalled()
            ->willReturn($this->slackSecret);

        $slackClient = self::prophesize(Client::class);
        $slackClient->chatPostMessage([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The CasRec Lay CSV has not been uploaded within the past 24 hours',
            ])
            ->shouldBeCalled();
        $this->slackClientFactory->createClient($this->slackSecret)
            ->shouldBeCalled()
            ->willReturn($slackClient->reveal());

        $result = $this->commandTester->execute([]);

        $this->assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    /**
     * @test
     */
    public function executeOnNonBankHolidaysWhereNonCSVExistsSlackIsPostedTo()
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(true);

        $this->secretManagerService->getSecret('opg-alerts-slack-token')
            ->shouldBeCalled()
            ->willReturn($this->slackSecret);

        $slackClient = self::prophesize(Client::class);
        $slackClient->chatPostMessage([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The CasRec Lay CSV has not been uploaded within the past 24 hours',
            ])
            ->shouldBeCalled();
        $slackClient->chatPostMessage([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The Sirius Lay CSV has not been uploaded within the past 24 hours',
            ])
            ->shouldBeCalled();
        $slackClient->chatPostMessage([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The CasRec Prof CSV has not been uploaded within the past 24 hours',
            ])
            ->shouldBeCalled();
        $slackClient->chatPostMessage([
              'username' => 'opg-alerts',
              'channel' => 'opg-digideps-team',
              'text' => ':cat_spin: The CasRec PA CSV has not been uploaded within the past 24 hours',
            ])
            ->shouldBeCalled();

        $this->slackClientFactory->createClient($this->slackSecret)
            ->shouldBeCalled()
            ->willReturn($slackClient->reveal());

        $result = $this->commandTester->execute([]);

        $this->assertEquals(0, $result, sprintf('Expected command to return 0, got %d', $result));
    }

    /**
     * @test
     */
    public function executeErrorIsLoggedIfCantGetAuditLogs()
    {
        $this->todayIsABankHoliday(false);
        $this->cannotRetrieveAuditLogs();

        $this->secretManagerService->getSecret('opg-alerts-slack-token')
            ->shouldBeCalled()
            ->willReturn($this->slackSecret);

        $slackClient = self::prophesize(Client::class);
        $slackClient->chatPostMessage(
            [
                'username' => 'opg-alerts',
                'channel' => 'opg-digideps-devs',
                'text' => 'Failed to retrieve audit logs during CSV upload check. Error message: The service cannot complete the request.',
            ]
        )->shouldBeCalled();

        $this->slackClientFactory->createClient($this->slackSecret)
            ->shouldBeCalled()
            ->willReturn($slackClient->reveal());

        $result = $this->commandTester->execute([]);

        $this->assertEquals(1, $result, sprintf('Expected command to return 1, got %d', $result));
        //Assert 1 is returned by command
    }

    /**
     * @test
     */
    public function executeErrorIsLoggedIfSlackPostIsNotSuccessful()
    {
        $this->todayIsABankHoliday(false);
        $this->cannotRetrieveAuditLogs();

        $this->secretManagerService->getSecret('opg-alerts-slack-token')
            ->shouldBeCalled()
            ->willReturn($this->slackSecret);

        $slackClient = self::prophesize(Client::class);

        $exception = new SlackErrorResponse('500', null);

        $slackClient->chatPostMessage(
            [
                'username' => 'opg-alerts',
                'channel' => 'opg-digideps-devs',
                'text' => 'Failed to retrieve audit logs during CSV upload check. Error message: The service cannot complete the request.',
            ]
        )->shouldBeCalled()->willThrow($exception);

        $this->slackClientFactory->createClient($this->slackSecret)
            ->shouldBeCalled()
            ->willReturn($slackClient->reveal());

        $this->logger->log('error', sprintf('Failed to post to Slack during CSV upload check: Slack returned error code "500"'))
            ->shouldBeCalled();

        $result = $this->commandTester->execute([]);

        $this->assertEquals(1, $result, sprintf('Expected command to return 1, got %d', $result));
    }

    /**
     * @test
     */
    public function executeErrorMessagePostedToSlackWhenUnableToRetrieveBankHolidays()
    {
        $this->now = new DateTime('01-02-2021');
        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($this->now);

        $this->unableToRetrieveBankHolidays();

        $this->secretManagerService->getSecret('opg-alerts-slack-token')
            ->shouldBeCalled()
            ->willReturn($this->slackSecret);

        $slackClient = self::prophesize(Client::class);

        $slackClient->chatPostMessage(
            [
                'username' => 'opg-alerts',
                'channel' => 'opg-digideps-devs',
                'text' => 'Failed to retrieve bank holidays from Gov.uk. Error message: Something went wrong oops',
            ]
        )->shouldBeCalled();

        $this->slackClientFactory->createClient($this->slackSecret)
            ->shouldBeCalled()
            ->willReturn($slackClient->reveal());

        $result = $this->commandTester->execute([]);

        $this->assertEquals(1, $result, sprintf('Expected command to return 1, got %d', $result));
    }

    private function todayIsABankHoliday(bool $isABankHoliday)
    {
        $this->bankHolidayAPI->getBankHolidays()->shouldBeCalled()->willReturn(
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

        $this->now = new DateTime($isABankHoliday ? '27-12-2021' : '01-02-2021');
        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($this->now);
    }

    private function aCsvUploadedEventExists(bool $exists, array $uploadedCSVs = [])
    {
        $startingTime = (int) (clone $this->now)->sub(new DateInterval('P1D'))->format('Uv');
        $endTime = (int) (clone $this->now)->format('Uv');

        if ($exists) {
            $this->awsAuditLogHandler->getLogStreams($this->auditLogGroupName)
                ->shouldBeCalled()
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
                    ])
                );

            $events = $this->populateLogEvents($uploadedCSVs);
            $expectedResponseFromAWS = new Result(
                [
                    'events' => $events,
                    'nextBackwardToken' => 'next-sequence-token',
                    'nextForwardToken' => 'next-sequence-token',
                ]
            );

            $this->awsAuditLogHandler->getLogEventsByLogStream(
                'CSV_UPLOADED',
                $startingTime,
                $endTime,
                $this->auditLogGroupName
            )
                ->shouldBeCalled()
                ->willReturn($expectedResponseFromAWS);
        } else {
            $this->awsAuditLogHandler->getLogStreams($this->auditLogGroupName)
                ->shouldBeCalled()
                ->willReturn([]);
        }
    }

    // Creates a Result object based on the CSV types passed in
    private function populateLogEvents(array $uploadedCSVs): array
    {
        $events = [];

        if (!empty($uploadedCSVs)) {
            foreach ($uploadedCSVs as $csv) {
                if (in_array($csv, $this->supportedCSVs)) {
                    list($source, $role) = explode(' ', $csv);
                    $events[] = [
                        'ingestionTime' => 1643206329732,
                        'message' => sprintf(
                            '{"message":"","context":{"trigger":"CSV_UPLOADED","source":"%s","role_type":"%s","}',
                            strtolower($source),
                            strtoupper($role)
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

    private function cannotRetrieveAuditLogs()
    {
        $startingTime = (int) (clone $this->now)->sub(new DateInterval('P1D'))->format('Uv');
        $endTime = (int) (clone $this->now)->format('Uv');

        $this->awsAuditLogHandler->getLogStreams($this->auditLogGroupName)
            ->shouldBeCalled()
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
                    ])
            );

        $exception = new AwsException(
            'The service cannot complete the request.',
            new Command('getLogEvents'),
            ['code' => 503]
        );

        $this->awsAuditLogHandler->getLogEventsByLogStream(
            'CSV_UPLOADED',
            $startingTime,
            $endTime,
            $this->auditLogGroupName
        )
            ->shouldBeCalled()
            ->willThrow($exception);
    }

    private function unableToRetrieveBankHolidays()
    {
        $this->bankHolidayAPI->getBankHolidays()
            ->shouldBeCalled()
            ->willThrow(new TransferException('Something went wrong oops'));
    }
}
