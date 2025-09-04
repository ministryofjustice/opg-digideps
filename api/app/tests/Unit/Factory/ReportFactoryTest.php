<?php

declare(strict_types=1);

namespace App\Factory;

use DateTimeZone;
use DateTime;
use App\Entity\Client;
use PHPUnit\Framework\TestCase;

class ReportFactoryTest extends TestCase
{
    private ReportFactory $sut;

    public function setUp(): void
    {
        $this->sut = new ReportFactory();
    }

    public function testCreate(): void
    {
        $tz = new DateTimeZone('Europe/London');

        $start = new DateTime('2025-08-21', timezone: $tz);

        // 364 days in the future (NB *not* 1 year: if the start date is in a leap year, this will be 2 days before the
        // day/month of the start date in the following year, rather than 1 day before)
        $expectedEnd = new DateTime('2026-08-20', timezone: $tz);

        $report = $this->sut->create(self::createStub(Client::class), 'OPG102', 'pfa', $start);

        self::assertEquals('102', $report->getType());
        self::assertEquals($start, $report->getStartDate());
        self::assertEquals($expectedEnd, $report->getEndDate());
    }
}
