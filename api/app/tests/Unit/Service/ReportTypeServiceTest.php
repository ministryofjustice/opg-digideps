<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Common\Report\ReportType;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Service\ReportTypeService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
        $this->expectException(\TypeError::class);
        /**
         * @phpstan-ignore-next-line intentinal misstyping for testing purposes.
         */
        ReportTypeService::determineReportType(['123', '456']);
    }

    #[Test]
    public function determineReportTypePassedEmptyArray(): void
    {
        $sut = ReportTypeService::determineReportType([]);

        $this->assertNull($sut);
    }
}
