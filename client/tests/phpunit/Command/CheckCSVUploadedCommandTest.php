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
    public function executeOnNonBankHolidaysWhenCSVsHaveBeenUploadedSlackIsNotPostedTo()
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(true);

        $this->secretManagerService->getSecret(Argument::any())->shouldNotBeCalled();
        $this->slackClientFactory->create(Argument::any())->shouldNotBeCalled();

        $this->commandTester->execute([]);
    }

    /**
     * @test
     */
    public function executeOnBankHolidaysSlackIsNotPostedTo()
    {
        $this->todayIsABankHoliday(true);

        $this->awsAuditLogHandler->getLogEventsByLogStream(Argument::cetera())->shouldNotBeCalled();
        $this->secretManagerService->getSecret(Argument::any())->shouldNotBeCalled();
        $this->slackClientFactory->create(Argument::any())->shouldNotBeCalled();

        $this->commandTester->execute([]);
    }

    /**
     * @test
     */
    public function executeOnNonBankHolidaysWhenCSVsHaveNotBeenUploadedSlackIsPostedTo()
    {
        $this->todayIsABankHoliday(false);
        $this->aCsvUploadedEventExists(false);

        $this->secretManagerService->getSecret('opg-response-slack-token')
            ->shouldBeCalled()
            ->willReturn($this->slackSecret);

        $slackClient = self::prophesize(Client::class);
        $slackClient->chatPostMessage([
            'username' => 'opg_response',
            'channel' => 'random',
            'text' => 'Hello world',
        ])
        ->shouldBeCalled();

        $this->slackClientFactory->createClient($this->slackSecret)
            ->shouldBeCalled()
            ->willReturn($slackClient->reveal());

        $this->commandTester->execute([]);
    }

    private function todayIsABankHoliday(bool $isABankHoliday)
    {
        $this->now = new DateTime($isABankHoliday ? '27-12-2021' : '01-02-2021');
        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($this->now);
    }

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
}
