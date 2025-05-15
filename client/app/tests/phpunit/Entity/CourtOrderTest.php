<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Report\Report;
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
            'Unsubmitted report' => [(new Report())->setSubmitted(true)->setUnSubmitDate(new \DateTime())],
        ];
    }

    /**
     * @dataProvider inactiveReportProvider
     */
    public function testGetActiveReportNull(?Report $report = null): void
    {
        $this->courtOrder->setReports($report ? [$report] : []);

        $result = $this->courtOrder->getActiveReport();

        $this->assertNull($result);
    }

    public function testGetActiveReportsOneActiveReport(): void
    {
        $report = new Report();
        $this->courtOrder->setReports([$report]);

        $result = $this->courtOrder->getActiveReport();

        $this->assertSame($report, $result);
    }

    public function testGetFirstActiveReport(): void
    {
        $inactiveReport = (new Report())->setSubmitted(true);
        $activeReport1 = new Report();
        $activeReport2 = new Report();

        $this->courtOrder->setReports([
            $inactiveReport,
            $activeReport1,
            $activeReport2,
        ]);

        $result = $this->courtOrder->getActiveReport();

        $this->assertSame($activeReport1, $result);
    }

    public static function unsubmittedReportProvider(): array
    {
        return [
            'No reports' => [],
            'Submitted report' => [(new Report())->setSubmitted(true)],
            'Report submitted and unsubmitted date set' => [(new Report())->setSubmitted(true)->setUnSubmitDate(new \DateTime())],
        ];
    }

    /**
     * @dataProvider unsubmittedReportProvider
     */
    public function testGetUnsubmittedReportNull(?Report $report = null): void
    {
        $this->courtOrder->setReports($report ? [$report] : []);

        $result = $this->courtOrder->getUnsubmittedReport();

        $this->assertNull($result);
    }

    public function testGetUnsubmittedReportOneUnsubmittedReport(): void
    {
        $report = (new Report())->setSubmitted(false)->setUnSubmitDate(new \DateTime());
        $this->courtOrder->setReports([$report]);

        $result = $this->courtOrder->getUnsubmittedReport();

        $this->assertSame($report, $result);
    }

    public function testGetFirstUnsubmittedReport(): void
    {
        $activeReport = new Report();
        $unsubmittedReport1 = (new Report())->setSubmitted(false)->setUnSubmitDate(new \DateTime());
        $unsubmittedReport2 = (new Report())->setSubmitted(false)->setUnSubmitDate(new \DateTime());

        $this->courtOrder->setReports([
            $activeReport,
            $unsubmittedReport1,
            $unsubmittedReport2,
        ]);

        $result = $this->courtOrder->getUnsubmittedReport();

        $this->assertSame($unsubmittedReport1, $result);
    }

    public function testGetSubmittedReportsNoSubmittedReports(): void
    {
        $result = $this->courtOrder->getSubmittedReports();

        $this->assertEquals([], $result);
    }

    public function testGetSubmittedReportsOnlySubmittedReportsReturned(): void
    {
        $activeReport = new Report();
        $submittedReport1 = (new Report())->setSubmitted(true);
        $unsubmittedReport = (new Report())->setSubmitted(false)->setUnSubmitDate(new \DateTime());
        $submittedReport2 = (new Report())->setSubmitted(true);

        $this->courtOrder->setReports([
            $activeReport,
            $submittedReport1,
            $unsubmittedReport,
            $submittedReport2,
        ]);

        $result = $this->courtOrder->getSubmittedReports();

        $this->assertEquals(
            [
                $submittedReport1,
                $submittedReport2,
            ],
            $result
        );
    }

    public static function hasCoDeputiesProvider(): array
    {
        return [
            'No deputies' => [[], false],
            'Only one deputy' => [[new Deputy()], false],
            'Multiple deputies' => [[new Deputy(), new Deputy()], true],
        ];
    }

    /**
     * @dataProvider hasCoDeputiesProvider
     *
     * @param Deputy[] $activeDeputies
     */
    public function testHasCoDeputiesNoDeputies(array $activeDeputies, bool $expectedResult): void
    {
        $this->courtOrder->setActiveDeputies($activeDeputies);

        $this->assertEquals($expectedResult, $this->courtOrder->hasCoDeputies());
    }

    public function testGetCoDeputiesWithNoDeputies(): void
    {
        $this->assertEquals([], $this->courtOrder->getCoDeputies('1234'));
    }

    public function testGetCoDeputiesExcludesLoggedInDeputyAndIsOrderedByFirstname()
    {
        $loggedInDeputy = (new Deputy())->setFirstname('Bill')->setDeputyUid('1234');
        $coDeputy1 = (new Deputy())->setFirstname('Zoé')->setDeputyUid('5678');
        $coDeputy2 = (new Deputy())->setFirstname('Floyd')->setDeputyUid('4321');

        $this->courtOrder->setActiveDeputies(
            [
                $loggedInDeputy,
                $coDeputy1,
                $coDeputy2,
            ]
        );

        $this->assertEquals(
            [
                $coDeputy2,
                $coDeputy1,
            ],
            $this->courtOrder->getCoDeputies('1234')
        );
    }

    public function testGetHybridCourtOrderType()
    {
        $hybridReport = (new Report())->setType('103-4');

        // both hybrid court orders are assigned a hw order type
        $pfaCourtOrder = (new CourtOrder())->setOrderType('hw');
        $hwCourtOrder = (new CourtOrder())->setOrderType('hw');

        $pfaCourtOrder->setReports([$hybridReport]);
        $hwCourtOrder->setReports([$hybridReport]);

        $courtOrderTypePfa = $pfaCourtOrder->getActiveReportType();
        $courtOrderTypeHw = $hwCourtOrder->getActiveReportType();

        $this->assertEquals('Property & Affairs with Health & Welfare Report', $courtOrderTypeHw);
        $this->assertEquals('Property & Affairs with Health & Welfare Report', $courtOrderTypePfa);
    }
}
