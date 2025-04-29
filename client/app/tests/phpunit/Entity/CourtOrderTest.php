<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Report\Report;
use DateTime;
use PHPUnit\Framework\TestCase;

class CourtOrderTest extends TestCase
{
    private CourtOrder $courtOrder;

    public function setUp(): void
    {
        $this->courtOrder = new CourtOrder();
    }

    public static function inactiveReportProvider(): array
    {
        return [
            'No reports' => [],
            'Submitted report' => [(new Report())->setSubmitted(true)],
            'Unsubmitted report' => [(new Report())->setSubmitted(true)->setUnSubmitDate(new DateTime())],
        ];
    }

    /**
     * @dataProvider inactiveReportProvider
     */
    public function testGetActiveReportNull(?Report $report = null)
    {
        $this->courtOrder->setReports($report ? [$report] : []);

        $result = $this->courtOrder->getActiveReport();

        $this->assertNull($result);
    }

    public function testGetActiveReportsOneActiveReport()
    {
        $report = new Report();
        $this->courtOrder->setReports([$report]);

        $result = $this->courtOrder->getActiveReport();

        $this->assertSame($report, $result);
    }

    public function testGetFirstActiveReport()
    {
        $inactiveReport = (new Report())->setSubmitted(true);
        $activeReport1 = new Report();
        $activeReport2 = new Report();

        $this->courtOrder->setReports([
            $inactiveReport,
            $activeReport1,
            $activeReport2
        ]);

        $result = $this->courtOrder->getActiveReport();

        $this->assertSame($activeReport1, $result);
    }
}
