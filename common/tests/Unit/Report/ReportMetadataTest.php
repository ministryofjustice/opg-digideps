<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Common\Unit\Report;

use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Common\Report\ReportMetadata;
use OPG\Digideps\Common\Report\ReportType;
use OPG\Digideps\Common\Report\Section\ReportSection;
use PHPUnit\Framework\TestCase;

class ReportMetadataTest extends TestCase
{
    public function testGetSectionLink(): void
    {
        $metadata = new ReportMetadata(42, new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Single,
            DeputyType::LAY
        ));
        $this->assertSame('other_info', $metadata->getSectionLink(ReportSection::OTHER_INFO)->url->name);
    }

    public function testGetSectionAfterLink(): void
    {
        $metadata = new ReportMetadata(42, new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Single,
            DeputyType::LAY
        ));
        $this->assertSame('documents', $metadata->getSectionAfterLink(ReportSection::OTHER_INFO)->url->name);
    }

    public function testGetOverviewLink(): void
    {
        $metadata = new ReportMetadata(42, new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Single,
            DeputyType::LAY
        ));
        $this->assertSame('report_overview', $metadata->getOverviewLink()->url->name);
    }

    public function testGetSectionBeforeLink(): void
    {
        $metadata = new ReportMetadata(42, new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Single,
            DeputyType::LAY
        ));
        $this->assertSame('actions', $metadata->getSectionBeforeLink(ReportSection::OTHER_INFO)->url->name);
    }

    public function testGetSectionLike(): void
    {
        $metadata = new ReportMetadata(42, new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Single,
            DeputyType::LAY
        ));
        $this->assertSame(ReportSection::DEPUTY_EXPENSES, $metadata->getSectionLike(ReportSection::PA_DEPUTY_EXPENSES));
        $this->assertSame(ReportSection::MONEY_OUT, $metadata->getSectionLike(ReportSection::MONEY_OUT_SHORT));
        $this->assertNull($metadata->getSectionLike(ReportSection::LIFESTYLE));
    }
}
