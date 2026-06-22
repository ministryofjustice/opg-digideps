<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\Assembler;

use Faker\Factory;
use Faker\Provider\en_GB\Address;
use PHPUnit\Framework\Attributes\Test;
use OPG\Digideps\Backend\Service\ReportUtils;
use OPG\Digideps\Backend\v2\Registration\Assembler\SiriusToOrgDeputyshipDtoAssembler;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class SiriusToOrgDeputyshipDtoAssemblerTest extends TestCase
{
    use ProphecyTrait;

    public static function generateValidSiriusOrgDeputyshipArray(): array
    {
        $faker = Factory::create();
        $courtOrderMadeDate = \DateTimeImmutable::createFromMutable($faker->dateTimeThisYear());
        $reportPeriodEndDate = $courtOrderMadeDate->modify('12 months - 1 day');

        return [
            'Case' => (string) $faker->randomNumber(8),
            'ClientForename' => $faker->firstName(),
            'ClientSurname' => $faker->lastName(),
            'ClientDateOfBirth' => $faker->dateTime()->format('Y-m-d'),
            'ClientAddress1' => $faker->buildingNumber() . ' ' . $faker->streetName(),
            'ClientAddress2' => Address::cityPrefix() . ' ' . $faker->city(),
            'ClientAddress3' => Address::county(),
            'ClientAddress4' => null,
            'ClientAddress5' => null,
            'ClientPostcode' => Address::postcode(),
            'DeputyUid' => (string) $faker->randomNumber(8),
            'DeputyType' => $faker->randomElement(['PRO', 'PA']),
            'DeputyEmail' => sprintf('%s@%s%s.com', $faker->userName(), $faker->randomNumber(8), $faker->domainWord()),
            'DeputyOrganisation' => $faker->company(),
            'DeputyForename' => $faker->firstName(),
            'DeputySurname' => $faker->lastName(),
            'DeputyAddress1' => $faker->streetName(),
            'DeputyAddress2' => Address::cityPrefix() . ' ' . $faker->city(),
            'DeputyAddress3' => $faker->city(),
            'DeputyAddress4' => Address::county(),
            'DeputyAddress5' => 'UK',
            'DeputyPostcode' => Address::postcode(),
            'MadeDate' => $courtOrderMadeDate->format('Y-m-d'),
            'LastReportDay' => $reportPeriodEndDate->format('Y-m-d'),
            'ReportType' => $faker->randomElement(['OPG102', 'OPG103', 'OPG104']),
            'OrderType' => $faker->randomElement(['pfa', 'hw']),
            'Hybrid' => 'SINGLE',
        ];
    }

    #[Test]
    public function assembleFromArrayProfPFAHighAssets(): void
    {
        $siriusArray = self::generateValidSiriusOrgDeputyshipArray();
        $siriusArray['LastReportDay'] = '2022-03-03';
        $siriusArray['DeputyType'] = 'PRO';
        $siriusArray['ReportType'] = 'OPG102';
        $siriusArray['OrderType'] = 'pfa';

        $expectedReportEndDate = \DateTime::createFromFormat('Y-m-d', $siriusArray['LastReportDay']);
        $expectedReportStartDate = \DateTime::createFromFormat('Y-m-d', '2021-03-04');
        $expectedClientDateOfBirth = \DateTime::createFromFormat('Y-m-d', $siriusArray['ClientDateOfBirth']);
        $expectedMadeDate = \DateTime::createFromFormat('Y-m-d', $siriusArray['MadeDate']);

        $reportUtils = self::prophesize(ReportUtils::class);

        $reportUtils->determineReportType($siriusArray['ReportType'], $siriusArray['OrderType'], $siriusArray['DeputyType'])
            ->shouldBeCalled()
            ->willReturn('102-5');

        $reportUtils->generateReportStartDateFromEndDate($expectedReportEndDate)->shouldBeCalled()->willReturn($expectedReportStartDate);

        $sut = new SiriusToOrgDeputyshipDtoAssembler($reportUtils->reveal());

        $dto = $sut->assembleSingleDtoFromArray($siriusArray);
        $dateTimeDob = $dto->getClientDateOfBirth() ?? new \DateTime();
        $dateTimeCourt = $dto->getCourtDate() ?? new \DateTime();
        $dateTimeReportStart = $dto->getReportStartDate() ?? new \DateTime();
        $dateTimeReportEnd = $dto->getReportEndDate() ?? new \DateTime();


        self::assertEquals($siriusArray['Case'], $dto->getCaseNumber());
        self::assertEquals($siriusArray['ClientForename'], $dto->getClientFirstname());
        self::assertEquals($siriusArray['ClientSurname'], $dto->getClientLastname());
        self::assertEquals($expectedClientDateOfBirth->format('Y-m-d'), $dateTimeDob->format('Y-m-d'));
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
        self::assertEquals($expectedMadeDate->format('Y-m-d'), $dateTimeCourt->format('Y-m-d'));
        self::assertEquals($expectedReportStartDate->format('Y-m-d'), $dateTimeReportStart->format('Y-m-d'));
        self::assertEquals($expectedReportEndDate->format('Y-m-d'), $dateTimeReportEnd->format('Y-m-d'));
        self::assertEquals('102-5', $dto->getReportType());
        self::assertEquals($siriusArray['Hybrid'], $dto->getHybrid());
    }

    #[Test]
    public function assembleFromArrayPALowAssetsHybridHW(): void
    {
        $siriusArray = self::generateValidSiriusOrgDeputyshipArray();
        $siriusArray['LastReportDay'] = '2022-01-10';
        $siriusArray['DeputyType'] = 'PA';
        $siriusArray['ReportType'] = 'OPG103';
        $siriusArray['OrderType'] = 'hw';

        $reportEndDate = \DateTime::createFromFormat('Y-m-d', $siriusArray['LastReportDay']);

        $reportUtils = self::prophesize(ReportUtils::class);

        $reportUtils->determineReportType($siriusArray['ReportType'], $siriusArray['OrderType'], $siriusArray['DeputyType'])
            ->shouldBeCalled()
            ->willReturn('103-4-6');

        $reportUtils->generateReportStartDateFromEndDate($reportEndDate)->shouldBeCalled();

        $sut = new SiriusToOrgDeputyshipDtoAssembler($reportUtils->reveal());

        $dto = $sut->assembleSingleDtoFromArray($siriusArray);

        self::assertEquals($reportEndDate, $dto->getReportEndDate());
        self::assertEquals('103-4-6', $dto->getReportType());
    }
}
