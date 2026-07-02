<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Common\Unit\Report;

use OPG\Digideps\Common\Report\ReportType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReportTypeTest extends TestCase
{
    private const array CASES = ['102', '103', '104', '102-4', '103-4'];

    #[Test]
    public function unexpectedReportTypeReturnsNull(): void
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
        foreach (self::CASES as $case) {
            yield [$case];
        }
    }

    public static function proReportTypeProvider(): \Generator
    {
        foreach (self::CASES as $case) {
            yield ["{$case}-5"];
        }
    }

    public static function paReportTypeProvider(): \Generator
    {
        foreach (self::CASES as $case) {
            yield ["{$case}-6"];
        }
    }
}
