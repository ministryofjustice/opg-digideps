<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\Assembler;

use App\Service\ReportUtils;
use App\Tests\Unit\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;
use App\v2\Registration\Assembler\CasRecToOrgDeputyshipDtoAssembler;
use DateTime;
use PHPUnit\Framework\TestCase;

class CasRecToOrgDeputyshipDtoAssemblerTest extends TestCase
{
    /** @test */
    public function assembleFromArrayDataIsSanitised()
    {
        $casrecArray = OrgDeputyshipDTOTestHelper::generateValidCasRecOrgDeputyshipArray();
        $casrecArray['Forename'] = '   Roisin  ';
        $casrecArray['Surname'] = ' Murphy     ';
        $casrecArray['Case'] = 'ABCD12';
        $casrecArray['Deputy No'] = '1234567';

        $lastReportDate = new DateTime($casrecArray['Last Report Day']);
        $now = new DateTime();

        $reportUtils = self::prophesize(ReportUtils::class);
        $reportUtils->convertTypeofRepAndCorrefToReportType($casrecArray['Typeofrep'], $casrecArray['Corref'], 'REALM_PROF')
            ->shouldBeCalled()
            ->willReturn('OPG102');
        $reportUtils->parseCsvDate($casrecArray['Last Report Day'], 20)
            ->shouldBeCalled()
            ->willReturn($lastReportDate);
        $reportUtils->parseCsvDate($casrecArray['Client Date of Birth'], 19)
            ->shouldBeCalled()
            ->willReturn($lastReportDate);
        $reportUtils->generateReportStartDateFromEndDate($lastReportDate)
            ->shouldBeCalled()
            ->willReturn($now);
        $reportUtils->padCasRecNumber('abcd12')
            ->shouldBeCalled()
            ->willReturn('00abcd12');

        $sut = new CasRecToOrgDeputyshipDtoAssembler($reportUtils->reveal());
        $dto = $sut->assembleSingleDtoFromArray($casrecArray);

        self::assertEquals('Roisin', $dto->getClientFirstname());
        self::assertEquals('Murphy', $dto->getClientLastname());
    }
}
