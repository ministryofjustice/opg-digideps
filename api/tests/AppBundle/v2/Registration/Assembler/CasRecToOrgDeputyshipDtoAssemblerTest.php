<?php declare(strict_types=1);

namespace Tests\AppBundle\v2\Registration\Assembler;

use AppBundle\v2\Registration\Assembler\CasRecToOrgDeputyshipDtoAssembler;
use AppBundle\v2\Registration\Converter\ReportTypeConverter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Tests\AppBundle\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;

class CasRecToOrgDeputyshipDtoAssemblerTest extends TestCase
{
    /** @test */
    public function assembleFromArray_report_type_is_inferred_from_Corref_and_Typeofrep()
    {
        $casrecArray = OrgDeputyshipDTOTestHelper::generateValidCasRecOrgDeputyshipArray();

        /** @var ReportTypeConverter|ObjectProphecy $converter */
        $converter = self::prophesize(ReportTypeConverter::class);
        $converter->convertTypeofRepAndCorrefToReportType(Argument::cetera())->shouldBeCalled()->willReturn('OPG102');

        $sut = new CasRecToOrgDeputyshipDtoAssembler($converter->reveal());

        $dto = $sut->assembleFromArray($casrecArray);
    }
}
