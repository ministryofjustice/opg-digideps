<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Domain\CourtOrder\CourtOrderReportType;
use App\Domain\CourtOrder\CourtOrderType;
use App\Domain\Deputy\DeputyType;
use App\Domain\Report\ReportType;
use App\Entity\CourtOrder;
use App\Service\ReportTypeService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

class ReportTypeServiceTest extends TestCase
{
    #[Test]
    public function determineReportTypeReturnsNullWhenPassedMultipleReportTypes(): void
    {
        $reportType1 = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Single,
            DeputyType::LAY
        );

        $courtOrder1 = $this->createMock(CourtOrder::class);
        $courtOrder1->method('getDesiredReportType')->willReturn($reportType1);

        $reportType2 = new ReportType(
            CourtOrderReportType::OPG103,
            CourtOrderType::PFA,
            CourtOrderKind::Single,
            DeputyType::LAY
        );

        $courtOrder2 = $this->createMock(CourtOrder::class);
        $courtOrder2->method('getDesiredReportType')->willReturn($reportType2);

        $sut = ReportTypeService::determineReportType([$courtOrder1, $courtOrder2]);

        $this->assertNull($sut);
    }

    #[Test]
    public function determineReportTypeReturnsReportType(): void
    {
        $reportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::HW,
            CourtOrderKind::Single,
            DeputyType::LAY
        );

        $courtOrder1 = $this->createMock(CourtOrder::class);
        $courtOrder1->method('getDesiredReportType')->willReturn($reportType);

        $courtOrder2 = $this->createMock(CourtOrder::class);
        $courtOrder2->method('getDesiredReportType')->willReturn($reportType);

        $sut = ReportTypeService::determineReportType([$courtOrder1, $courtOrder2]);

        $this->assertEquals($reportType, $sut);
    }

    #[Test]
    public function determineReportTypePassedNonCourtOrderObjectArray(): void
    {
        try {
            $sut = ReportTypeService::determineReportType(['123', '456']);
        } catch (\TypeError $e) {
            $this->assertInstanceOf(TypeError::class, $e);
        }
    }

    #[Test]
    public function determineReportTypePassedEmptyArray(): void
    {
        $sut = ReportTypeService::determineReportType([]);

        $this->assertNull($sut);
    }
}
