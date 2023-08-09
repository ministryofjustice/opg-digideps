<?php

declare(strict_types=1);

namespace App\Tests\Behat\App\Service\Csv;

use App\Service\Csv\CsvBuilder;
use App\Service\Csv\ReportImbalanceCsvGenerator;
use PHPUnit\Framework\TestCase;

class ReportImbalanceCsvGeneratorTest extends TestCase
{

    /** @test */
    public function generateImbalanceReportResponsesCsv()
    {
        $imbalanceMetrics = [
            [
                "deputy_type" => "LAY",
                "no_imbalance" => 0,
                "reported_imbalance" => 0,
                "imbalance_percent" => 0
            ],
            [
                "deputy_type" => "PRO",
                "no_imbalance" => 0,
                "reported_imbalance" => 0,
                "imbalance_percent" => 0
            ],
        ];

        $expectedCsv = <<<CSV
        "Deputy Type","No Imbalance Reported","Imbalance Reported","Imbalance %","Total Submitted"
        "LAY", 0,0,0
        "PRO", 0,0,0
        CSV;

        $reportData = new ReportImbalanceCsvGenerator(new CsvBuilder());
        self::assertEquals($expectedCsv, $reportData->generateReportImbalanceCsv($imbalanceMetrics));
    }
}
