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
        $siriusArray['DeputyType'] = 'PRO';
        $siriusArray['ReportType'] = 'OPG102';
        $siriusArray['OrderType'] = 'pfa';

        $expectedReportEndDate = new DateTime($siriusArray['LastReportDay']);
        $expectedReportStartDate = new DateTime('04/03/2021');

        $reportUtils = self::prophesize(ReportUtils::class);

        $reportUtils->determineReportType($siriusArray['ReportType'], $siriusArray['OrderType'], $siriusArray['DeputyType'])
            ->shouldBeCalled()
            ->willReturn('102-5');

        $reportUtils->generateReportStartDateFromEndDate($expectedReportEndDate)->shouldBeCalled();
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
        self::assertEquals($siriusArray['DeputyUid'], $dto->getDeputyUid());
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
        self::assertEquals($expectedReportStartDate, $dto->getReportStartDate());
        self::assertEquals($expectedReportEndDate, $dto->getReportEndDate());
        self::assertEquals('102-5', $dto->getReportType());
    }

    /** @test */
    public function assembleFromArrayPALowAssetsHybridHW()
    {
        $siriusArray = OrgDeputyshipDTOTestHelper::generateValidSiriusOrgDeputyshipArray();
        $siriusArray['LastReportDay'] = '10/01/2022';
        $siriusArray['DeputyType'] = 'PA';
        $siriusArray['ReportType'] = 'OPG103';
        $siriusArray['OrderType'] = 'hw';

        $reportEndDate = new DateTime($siriusArray['LastReportDay']);

        $reportUtils = self::prophesize(ReportUtils::class);

        $reportUtils->determineReportType($siriusArray['ReportType'], $siriusArray['OrderType'], $siriusArray['DeputyType'])
            ->shouldBeCalled()
            ->willReturn('103-4-6');

        $reportUtils->generateReportStartDateFromEndDate($reportEndDate)->shouldBeCalled();
        $reportUtils->padCasRecNumber($siriusArray['Case'])->shouldBeCalled();

        $sut = new SiriusToOrgDeputyshipDtoAssembler($reportUtils->reveal());

        $dto = $sut->assembleSingleDtoFromArray($siriusArray);

        self::assertEquals($reportEndDate, $dto->getReportEndDate());
        self::assertEquals('103-4-6', $dto->getReportType());
    }
}
