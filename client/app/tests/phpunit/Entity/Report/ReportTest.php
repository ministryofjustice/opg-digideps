<?php

declare(strict_types=1);

namespace Tests\App\Entity\Report;

use App\Entity\Report\Report;
use PHPUnit\Framework\TestCase;

class ReportTest extends TestCase
{
    /**
     * @dataProvider typeProvider
     *
     * @test
     */
    public function determineReportType(Report $report, string $expectedType)
    {
        self::assertEquals($expectedType, $report->determineReportType());
    }

    /**
     * @dataProvider typeDefinition
     *
     * @test
     */
    public function getReportTypeDefinition(Report $report, string $expectedType)
    {
        self::assertEquals($expectedType, $report->getReportTypeDefinition());
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

    public function typeDefinition()
    {
        return [
            'HW - Lay' => [(new Report())->setType(Report::TYPE_HEALTH_WELFARE), 'Health and Welfare Report'],
            'HW - Professional' => [(new Report())->setType('104-5'), 'Health and Welfare Report'],
            'PF - Lay' => [(new Report())->setType(Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS), 'Property & Affairs Report'],
            'PF - Pubic Authority' => [(new Report())->setType('102-6'), 'Property & Affairs Report'],
            'Combined - low' => [(new Report())->setType(Report::TYPE_COMBINED_LOW_ASSETS), 'Property & Affairs with Health & Welfare Report'],
            'Combined - low - Professional' => [(new Report())->setType('103-4-5'), 'Property & Affairs with Health & Welfare Report'],
        ];
    }
}
