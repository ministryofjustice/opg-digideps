<?php

namespace App\Tests\Command;

use App\Command\CheckCSVUploadedCommand;
use App\Service\Audit\AwsAuditLogHandler;
use App\Service\Client\GovUK\BankHolidaysAPIClient;
use App\Service\Time\DateTimeProvider;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CheckCSVUploadedCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function execute()
    {
        /** @var ObjectProphecy|BankHolidaysAPIClient $bankHolidayAPI */
        $bankHolidayAPI = self::prophesize(BankHolidaysAPIClient::class);
        $bankHolidayAPI->getBankHolidays()->shouldBeCalled()->willReturn(
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

        /** @var ObjectProphecy|DateTimeProvider $dateTimeProvider */
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $now = new \DateTime();
        $dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($now);

        /** @var ObjectProphecy|AwsAuditLogHandler $awsAuditLogHandler */
        $awsAuditLogHandler = self::prophesize(AwsAuditLogHandler::class);
        $startingTime = (int) $now->sub(new DateInterval('P1D'))->format('Uv');
        $awsAuditLogHandler->getLogEventsByLogStream(
            'CSV_UPLOADED',
            $startingTime,
            $now->format('Uv')
        )->shouldBeCalled()->willReturn(
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

        $sut = new CheckCSVUploadedCommand($bankHolidayAPI->reveal(), $dateTimeProvider->reveal());
    }
}
