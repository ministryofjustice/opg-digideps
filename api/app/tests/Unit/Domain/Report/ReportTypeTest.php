<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Domain\Report;

use OPG\Digideps\Backend\Domain\Report\ReportType;
use OPG\Digideps\Backend\Entity\Report\Report;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReportTypeTest extends TestCase
{
    #[Test]
    public function unexpectedReportTypeReturnsNull()
    {
        $reportType = ReportType::tryFrom('107-01');
        $this->assertNull($reportType);
    }

    #[DataProvider('layReportTypeProvider')]
    #[DataProvider('proReportTypeProvider')]
    #[DataProvider('paReportTypeProvider')]
    #[Test]
    public function testTryFromReturnsValid(string $type): void
    {
        $reportType = ReportType::tryFrom($type);

        $this->assertInstanceOf(ReportType::class, $reportType);
        $this->assertEquals((string) $reportType, $type);
    }

    public static function layReportTypeProvider(): \Generator
    {
        $cases = [
            Report::LAY_PFA_LOW_ASSETS_TYPE,
            Report::LAY_PFA_HIGH_ASSETS_TYPE,
            Report::LAY_HW_TYPE,
            Report::LAY_COMBINED_LOW_ASSETS_TYPE,
            Report::LAY_COMBINED_HIGH_ASSETS_TYPE,
        ];

        foreach ($cases as $case) {
            yield [$case];
        }
    }

    public static function proReportTypeProvider(): \Generator
    {
        $cases = [
            Report::PROF_PFA_LOW_ASSETS_TYPE,
            Report::PROF_PFA_HIGH_ASSETS_TYPE,
            Report::PROF_HW_TYPE,
            Report::PROF_COMBINED_LOW_ASSETS_TYPE,
            Report::PROF_COMBINED_HIGH_ASSETS_TYPE,
        ];

        foreach ($cases as $case) {
            yield [$case];
        }
    }

    public static function paReportTypeProvider(): \Generator
    {
        $cases = [
            Report::PA_PFA_LOW_ASSETS_TYPE,
            Report::PA_PFA_HIGH_ASSETS_TYPE,
            Report::PA_HW_TYPE,
            Report::PA_COMBINED_LOW_ASSETS_TYPE,
            Report::PA_COMBINED_HIGH_ASSETS_TYPE
        ];

        foreach ($cases as $case) {
            yield [$case];
        }
    }
}
