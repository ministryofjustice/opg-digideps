<?php declare(strict_types=1);

namespace Tests\AppBundle\v2\Registration\Assembler;

use AppBundle\Service\ReportUtils;
use AppBundle\v2\Registration\Assembler\CasRecToOrgDeputyshipDtoAssembler;
use AppBundle\v2\Registration\Converter\ReportTypeConverter;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\AppBundle\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;

class CasRecToOrgDeputyshipDtoAssemblerTest extends TestCase
{
    /** @test */
    public function assembleFromArray_report_type_and_dates_are_inferred_from_csv_array()
    {
        $casrecArray = OrgDeputyshipDTOTestHelper::generateValidCasRecOrgDeputyshipArray();
        $lastReportDate = new DateTime($casrecArray['Last Report Day']);
        $now = new DateTime();

        /** @var ReportTypeConverter|ObjectProphecy $converter */
        $reportUtils = self::prophesize(ReportUtils::class);
        $reportUtils->convertTypeofRepAndCorrefToReportType($casrecArray['Typeofrep'], $casrecArray['Corref'], 'REALM_PROF')
            ->shouldBeCalled()
            ->willReturn('OPG102');
        $reportUtils->parseCsvDate($casrecArray['Last Report Day'], 20)
            ->shouldBeCalled()
            ->willReturn($lastReportDate);
        $reportUtils->generateReportStartDateFromEndDate($lastReportDate)
            ->shouldBeCalled()
            ->willReturn($now);

        $sut = new CasRecToOrgDeputyshipDtoAssembler($reportUtils->reveal());
        $sut->assembleSingleDtoFromArray($casrecArray);
    }
}
