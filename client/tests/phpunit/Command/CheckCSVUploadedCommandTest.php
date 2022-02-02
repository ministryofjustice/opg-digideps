<?php

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
use JoliCode\Slack\Api\Client;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
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
    private CommandTester $commandTester;
    private DateTime $now;
    private string $slackSecret;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->bankHolidayAPI = self::prophesize(BankHolidaysAPIClient::class);
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->awsAuditLogHandler = self::prophesize(AwsAuditLogHandler::class);
        $this->secretManagerService = self::prophesize(SecretManagerService::class);
        $this->slackClientFactory = self::prophesize(ClientFactory::class);

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

        $sut = new CheckCSVUploadedCommand(
            $this->bankHolidayAPI->reveal(),
            $this->dateTimeProvider->reveal(),
            $this->secretManagerService->reveal(),
            $this->slackClientFactory->reveal(),
            $this->awsAuditLogHandler->reveal()
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
        $this->allCSVsHaveBeenUploaded();

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

        $this->secretManagerService->getSecret('opg-response-slack-token')
            ->shouldBeCalled()
            ->willReturn($this->slackSecret);

        $slackClient = self::prophesize(Client::class);
        $slackClient->chatPostMessage([
                                          'username' => 'opg_response',
                                          'channel' => 'opg-digideps-team',
                                          'text' => 'The CasRec Lay CSV has not been uploaded within the past 24 hours',
                                      ])
            ->shouldBeCalled();
        $slackClient->chatPostMessage([
                                          'username' => 'opg_response',
                                          'channel' => 'opg-digideps-team',
                                          'text' => 'The Sirius Lay CSV has not been uploaded within the past 24 hours',
                                      ])
            ->shouldBeCalled();
        $slackClient->chatPostMessage([
                                          'username' => 'opg_response',
                                          'channel' => 'opg-digideps-team',
                                          'text' => 'The CasRec Prof CSV has not been uploaded within the past 24 hours',
                                      ])
            ->shouldBeCalled();
        $slackClient->chatPostMessage([
                                          'username' => 'opg_response',
                                          'channel' => 'opg-digideps-team',
                                          'text' => 'The CasRec PA CSV has not been uploaded within the past 24 hours',
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
        //Test for when one type of CSV has not been uploaded
        //Creating one for each type of CSV provides enough coverage?
        //Assert 0 is returned by command
    }

    /**
     * @test
     */
    public function executeErrorIsLoggedIfCantGetAuditLogs()
    {
        $this->todayIsABankHoliday(false);
        //Create a mock function for aws audit log error
        //$this->aCsvUploadedEventExists(true);

        $this->secretManagerService->getSecret(Argument::any())->shouldNotBeCalled();

        //If we can't get logs, do we want to error our or should we post to slack that there was a problem?
        $this->slackClientFactory->createClient(Argument::any())->shouldNotBeCalled();
        $slackClient = self::prophesize(Client::class);
        $slackClient->chatPostMessage(Argument::any())->shouldNotBeCalled();

        $result = $this->commandTester->execute([]);

        $this->assertEquals(1, $result, sprintf('Expected command to return 1, got %d', $result));
        //Assert 1 is returned by command
    }

    /**
     * @test
     */
    public function executeErrorIsLoggedIfSlackPostIsNotSuccessful()
    {
        //Assert 1 is returned by command
    }

    private function todayIsABankHoliday(bool $isABankHoliday)
    {
        $this->now = new DateTime($isABankHoliday ? '27-12-2021' : '01-02-2021');
        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($this->now);
    }

    // Need to refactor this - either include an appropriate csv message or repurpose for non-csv event message
    private function aCsvUploadedEventExists(bool $exists)
    {
        $startingTime = (int) (clone $this->now)->sub(new DateInterval('P1D'))->format('Uv');
        $endTime = (int) (clone $this->now)->format('Uv');

        if ($exists) {
            $expectedResponseFromAWS = new Result(
                [
                    'events' => [
                        [
                            'ingestionTime' => 1643206329732,
                            'message' => 'something',
                            'timestamp' => 1643206329733,
                        ],
                    ],
                    'nextBackwardToken' => 'next-sequence-token',
                    'nextForwardToken' => 'next-sequence-token',
                ]
            );

            $this->awsAuditLogHandler->getLogEventsByLogStream(
                'CSV_UPLOADED',
                $startingTime,
                $endTime
            )
                ->shouldBeCalled()
                ->willReturn($expectedResponseFromAWS);
        } else {
            $exception = new AwsException(
                'The specified log group does not exist',
                new Command('getLogEvents'),
                ['code' => 400]
            );

            $this->awsAuditLogHandler->getLogEventsByLogStream(
                'CSV_UPLOADED',
                $startingTime,
                $endTime
            )
                ->shouldBeCalled()
                ->willThrow($exception);
        }
    }

    private function allCSVsHaveBeenUploaded()
    {
        $startingTime = (int) (clone $this->now)->sub(new DateInterval('P1D'))->format('Uv');
        $endTime = (int) (clone $this->now)->format('Uv');

        $expectedResponseFromAWS = new Result(
            [
                'events' => [
                    [
                        'ingestionTime' => 1643206329732,
                        'message' => '{"message":"","context":{"trigger":"CSV_UPLOADED","source":"casrec","role_type":"LAY","}',
                        'timestamp' => 1643206329733,
                    ],
                    [
                        'ingestionTime' => 1643206329734,
                        'message' => '{"message":"","context":{"trigger":"CSV_UPLOADED","source":"sirius","role_type":"LAY","}',
                        'timestamp' => 1643206329735,
                    ],
                    [
                        'ingestionTime' => 1643206329736,
                        'message' => '{"message":"","context":{"trigger":"CSV_UPLOADED","source":"casrec","role_type":"PROF","}',
                        'timestamp' => 1643206329737,
                    ],
                    [
                        'ingestionTime' => 1643206329738,
                        'message' => '{"message":"","context":{"trigger":"CSV_UPLOADED","source":"casrec","role_type":"PA","}',
                        'timestamp' => 1643206329739,
                    ],
                ],
                'nextBackwardToken' => 'next-sequence-token',
                'nextForwardToken' => 'next-sequence-token',
            ]
        );

        $this->awsAuditLogHandler->getLogEventsByLogStream(
            'CSV_UPLOADED',
            $startingTime,
            $endTime
        )
            ->shouldBeCalled()
            ->willReturn($expectedResponseFromAWS);
    }
}
