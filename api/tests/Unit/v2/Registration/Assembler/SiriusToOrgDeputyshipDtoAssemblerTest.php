<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\Assembler;

use App\Service\ReportUtils;
use App\Tests\Unit\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;
use App\v2\Registration\Assembler\SiriusToOrgDeputyshipDtoAssembler;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Validator\Constraints\Date;

class SiriusToOrgDeputyshipDtoAssemblerTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function assembleFromArrayProfPFAHighAssets()
    {
        $siriusArray = OrgDeputyshipDTOTestHelper::generateValidSiriusOrgDeputyshipArray();
        $siriusArray['LastReportDay'] = '03/03/2022';
        //$siriusArray['DeputyType'] = 'PRO';
        //$siriusArray['ReportType'] = 'OPG102';
        //$siriusArray['OrderType'] = 'PFA';

        $reportEndDate = new DateTime($siriusArray['LastReportDay']);
        $reportStartDate = new DateTime('04/03/2021');

        $reportUtils = self::prophesize(ReportUtils::class);

        // Prophecize determining report after merging in with Alex's branch
//        $reportUtils->convertTypeofRepAndCorrefToReportType($siriusArray['Typeofrep'], $siriusArray['Corref'], 'REALM_PROF')
//            ->shouldBeCalled()
//            ->willReturn('102-5');
        //$reportType = '102-5'

        $reportUtils->generateReportStartDateFromEndDate($reportEndDate)->shouldBeCalled();
        $reportUtils->padCasRecNumber($siriusArray['Case'])->shouldBeCalled();

        $sut = new SiriusToOrgDeputyshipDtoAssembler($reportUtils->reveal());

        $dto = $sut->assembleSingleDtoFromArray($siriusArray);

        self::assertEquals($siriusArray['Case'], $dto->getCaseNumber());
        self::assertEquals($siriusArray['ClientForename'], $dto->getClientFirstname());
        self::assertEquals($siriusArray['ClientSurname'], $dto->getClientLastname());
        self::assertEquals($siriusArray['ClientDateOfBirth'], $dto->getClientDateOfBirth());
        self::assertEquals($siriusArray['ClientAddress1'], $dto->getClientAddress1());
        self::assertEquals($siriusArray['ClientAddress2'], $dto->getClientAddress2());
        self::assertEquals($siriusArray['ClientAddress3'], $dto->getClientAddress3());
        self::assertEquals($siriusArray['ClientAddress4'], $dto->getClientAddress4());
        self::assertEquals($siriusArray['ClientAddress5'], $dto->getClientAddress5());
        self::assertEquals($siriusArray['ClientPostcode'], $dto->getClientPostCode());
        self::assertEquals($siriusArray['DeputyUid'], $dto->getDeputyUUID());
        self::assertEquals($siriusArray['DeputyEmail'], $dto->getDeputyEmail());
        self::assertEquals($siriusArray['DeputyForename'], $dto->getDeputyFirstname());
        self::assertEquals($siriusArray['DeputySurname'], $dto->getDeputyLastname());
        self::assertEquals($siriusArray['DeputyAddress1'], $dto->getDeputyAddress1());
        self::assertEquals($siriusArray['DeputyAddress2'], $dto->getDeputyAddress2());
        self::assertEquals($siriusArray['DeputyAddress3'], $dto->getDeputyAddress3());
        self::assertEquals($siriusArray['DeputyAddress4'], $dto->getDeputyAddress4());
        self::assertEquals($siriusArray['DeputyAddress5'], $dto->getDeputyAddress5());
        self::assertEquals($siriusArray['DeputyPostcode'], $dto->getDeputyPostcode());
        self::assertEquals(new Date($siriusArray['MadeDate']), $dto->getCourtDate());
        self::assertEquals($reportStartDate, $dto->getReportStartDate());
        self::assertEquals($reportEndDate, $dto->getReportEndDate());
        //self::assertEquals($reportType, $dto->getReportType());
    }

    /** @test */
    public function assembleFromArrayPALowAssetsHybridHW()
    {
        $siriusArray = OrgDeputyshipDTOTestHelper::generateValidSiriusOrgDeputyshipArray();
        $siriusArray['LastReportDay'] = '10/01/2022';
        //$siriusArray['DeputyType'] = 'PA';
        //$siriusArray['ReportType'] = 'OPG103';
        //$siriusArray['OrderType'] = 'HW';

        $reportEndDate = new DateTime($siriusArray['LastReportDay']);

        $reportUtils = self::prophesize(ReportUtils::class);

        // Prophecize determining report after merging in with Alex's branch
//        $reportUtils->convertTypeofRepAndCorrefToReportType($siriusArray['Typeofrep'], $siriusArray['Corref'], 'REALM_PROF')
//            ->shouldBeCalled()
//            ->willReturn('103-4-6');
        //$reportType = '103-4-6'

        $reportUtils->generateReportStartDateFromEndDate($reportEndDate)->shouldBeCalled();
        $reportUtils->padCasRecNumber($siriusArray['Case'])->shouldBeCalled();

        $sut = new SiriusToOrgDeputyshipDtoAssembler($reportUtils->reveal());

        $dto = $sut->assembleSingleDtoFromArray($siriusArray);

        self::assertEquals($reportEndDate, $dto->getReportEndDate());
        //self::assertEquals($reportType, $dto->getReportType());
    }

    /** @test */
    public function assembleFromArrayDataIsSanitised()
    {
        $siriusArray = OrgDeputyshipDTOTestHelper::generateValidSiriusOrgDeputyshipArray();
        $siriusArray['Case'] = 'ABCD12';
        $siriusArray['ClientForename'] = '   Claire  ';
        $siriusArray['ClientSurname'] = ' Murphy     ';

        $lastReportDate = new DateTime($siriusArray['LastReportDay']);

        $reportUtils = self::prophesize(ReportUtils::class);

        // Prophecize determining report after merging in with Alex's branch
//        $reportUtils->convertTypeofRepAndCorrefToReportType($siriusArray['Typeofrep'], $siriusArray['Corref'], 'REALM_PROF')
//            ->shouldBeCalled()
//            ->willReturn('OPG102');
        $reportUtils->generateReportStartDateFromEndDate($lastReportDate)
            ->shouldBeCalled();
        $reportUtils->padCasRecNumber('ABCD12')
            ->shouldBeCalled();

        $sut = new SiriusToOrgDeputyshipDtoAssembler($reportUtils->reveal());
        $dto = $sut->assembleSingleDtoFromArray($siriusArray);

        self::assertEquals('00ABCD12', $dto->getCaseNumber());
        self::assertEquals('Claire', $dto->getClientFirstname());
        self::assertEquals('Murphy', $dto->getClientLastname());
    }
}
