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
use PHPUnit\Framework\TestCase;

class ReportTypeServiceTest extends TestCase
{
    public function determineReportTypeReturnsNull(): void
    {
        $courtOrder1 = $this->createMock(CourtOrder::class);
        $courtOrder1->method('getDesiredReportType')->willReturn('123');

        $courtOrder2 = $this->createMock(CourtOrder::class);
        $courtOrder2->method('getDesiredReportType')->willReturn('456');

        $sut = ReportTypeService::determineReportType([$courtOrder1, $courtOrder2]);

        $this->assertNull($sut);
    }

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

        $this->assertObjectEquals($reportType, $sut);
    }

    public function determineReportTypePassedNonCourtOrderObjectArray(): void
    {
        $sut = ReportTypeService::determineReportType(['123', '456']);

        $this->throwException(new \InvalidArgumentException());
    }
}
