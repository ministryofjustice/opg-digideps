<?php

declare(strict_types=1);

namespace Tests\App\Entity\Report;

use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\Report;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    /**
     * @dataProvider typeProvider
     * @test
     */
    public function determineReportType(Report $report, string $expectedType)
    {
        self::assertEquals($expectedType, $report->determineReportType());
    }

    public function typeProvider()
    {
        return [
            'HW' => [(new Report())->setType(Report::TYPE_HEALTH_WELFARE), 'HW'],
            'PF - low' => [(new Report())->setType(Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS), 'PF'],
            'PF - high' => [(new Report())->setType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS), 'PF'],
            'Combined - low' => [(new Report())->setType(Report::TYPE_COMBINED_LOW_ASSETS), 'COMBINED'],
            'Combined - high' => [(new Report())->setType(Report::TYPE_COMBINED_HIGH_ASSETS), 'COMBINED'],
        ];
    }

    /**
     * @test
     * @dataProvider benefitsCheckSectionRequiredProvider
     */
    public function requiresBenefitsCheckSection(
        DateTime $featureFlagDate,
        DateTime $dueDate,
        ?ClientBenefitsCheck $clientBenefitSection,
        bool $expectedResult,
        ?DateTime $unsubmitDate
    ) {
        $report = (new Report())
            ->setDueDate($dueDate)
            ->setClientBenefitsCheck($clientBenefitSection)
            ->setUnSubmitDate($unsubmitDate);

        self::assertEquals(
            $expectedResult,
            $report->requiresBenefitsCheckSection($featureFlagDate)
        );
    }

    public function benefitsCheckSectionRequiredProvider(): array
    {
        $featureFlagDate = new DateTimeImmutable('01/01/2021');
        $unsubmitDate = new DateTime();

        return [
            'Due date 61 days after feature launch date' => [
                DateTime::createFromImmutable($featureFlagDate),
                DateTime::createFromImmutable($featureFlagDate->modify('-61 days')),
                null,
                true,
                null,
            ],
            'Due date 60 days after feature launch date' => [
                DateTime::createFromImmutable($featureFlagDate),
                DateTime::createFromImmutable($featureFlagDate->modify('-60 days')),
                null,
                false,
                null,
            ],
            'Due date 1 day before feature launch date' => [
                DateTime::createFromImmutable($featureFlagDate),
                DateTime::createFromImmutable($featureFlagDate->modify('+1 days')),
                null,
                false,
                null,
            ],
            'Due date 61 days after feature launch date, report unsubmitted but section not previously completed' => [
                DateTime::createFromImmutable($featureFlagDate),
                DateTime::createFromImmutable($featureFlagDate->modify('-61 days')),
                null,
                false,
                $unsubmitDate,
            ],
            'Due date 61 days after feature launch date, report unsubmitted but section previously completed' => [
                DateTime::createFromImmutable($featureFlagDate),
                DateTime::createFromImmutable($featureFlagDate->modify('-61 days')),
                new ClientBenefitsCheck(),
                true,
                $unsubmitDate,
            ],
            'Due date 1 day before feature launch date, report unsubmitted but section previously completed' => [
                DateTime::createFromImmutable($featureFlagDate),
                DateTime::createFromImmutable($featureFlagDate->modify('+1 days')),
                new ClientBenefitsCheck(),
                true,
                $unsubmitDate,
            ],
        ];
    }
}
