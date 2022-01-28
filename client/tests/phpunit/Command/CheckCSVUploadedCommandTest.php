<?php

namespace App\Tests\Command;

use App\Command\CheckCSVUploadedCommand;
use App\Service\Audit\AwsAuditLogHandler;
use App\Service\Client\GovUK\BankHolidaysAPIClient;
use App\Service\SecretManagerService;
use App\Service\Time\DateTimeProvider;
use Aws\Result;
use DateInterval;
use DateTime;
use JoliCode\Slack\ClientFactory;
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
    }

    /**
     * @test
     */
    public function execute()
    {
        $now = new DateTime('01-02-2021');
        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($now);

        $startingTime = (int) (clone $now)->sub(new DateInterval('P1D'))->format('Uv');
        $endTime = (int) (clone $now)->format('Uv');

        $this->awsAuditLogHandler->getLogEventsByLogStream(
            'CSV_UPLOADED',
            $startingTime,
            $endTime
        )
            ->shouldBeCalled()
            ->willReturn(
            new Result(
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
            ),
        );

        $this->secretManagerService->getSecret(Argument::any())->shouldNotBeCalled();
        $this->slackClientFactory->create(Argument::any())->shouldNotBeCalled();

        $this->commandTester->execute([]);
    }
}
