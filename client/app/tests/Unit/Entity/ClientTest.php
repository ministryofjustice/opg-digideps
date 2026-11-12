<?php

declare(strict_types=1);

use OPG\Digideps\Frontend\Entity\Client;
use OPG\Digideps\Frontend\Entity\CourtOrder;
use OPG\Digideps\Frontend\Entity\Deputy;
use OPG\Digideps\Frontend\Entity\Report\Report;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testHasProDeputy(): void
    {
        $deputy = new Deputy();
        $deputy->deputyType = 'PRO';

        $activeCourtOrder = new CourtOrder();
        $activeCourtOrder->setStatus('ACTIVE');

        $inactiveCourtOrder = new CourtOrder();
        $inactiveCourtOrder->setStatus('CLOSED');

        $client = new Client();
        $client->courtOrders = [$activeCourtOrder, $inactiveCourtOrder];

        // deputy only active on the inactive court order
        $activeCourtOrder->setActiveDeputies([]);
        $inactiveCourtOrder->setActiveDeputies([$deputy]);
        self::assertFalse($client->hasProDeputy());

        // deputy only active on the active court order
        $activeCourtOrder->setActiveDeputies([$deputy]);
        $inactiveCourtOrder->setActiveDeputies([]);
        self::assertTrue($client->hasProDeputy());

        // deputy active on both court orders
        $activeCourtOrder->setActiveDeputies([$deputy]);
        $inactiveCourtOrder->setActiveDeputies([]);
        self::assertTrue($client->hasProDeputy());
    }

    public function testHasPaDeputy(): void
    {
        $deputy = new Deputy();
        $deputy->deputyType = 'PA';

        $activeCourtOrder = new CourtOrder();
        $activeCourtOrder->setStatus('ACTIVE');

        $inactiveCourtOrder = new CourtOrder();
        $inactiveCourtOrder->setStatus('CLOSED');

        $client = new Client();
        $client->courtOrders = [$activeCourtOrder, $inactiveCourtOrder];

        // deputy only active on the inactive court order
        $activeCourtOrder->setActiveDeputies([]);
        $inactiveCourtOrder->setActiveDeputies([$deputy]);
        self::assertFalse($client->hasPaDeputy());

        // deputy only active on the active court order
        $activeCourtOrder->setActiveDeputies([$deputy]);
        $inactiveCourtOrder->setActiveDeputies([]);
        self::assertTrue($client->hasPaDeputy());

        // deputy active on both court orders
        $activeCourtOrder->setActiveDeputies([$deputy]);
        $inactiveCourtOrder->setActiveDeputies([]);
        self::assertTrue($client->hasPaDeputy());
    }

    public static function courtOrderCombos(): array
    {
        return [
            ['hwPresent' => false, 'hwActive' => false, 'pfaPresent' => false, 'pfaActive' => false],
            ['hwPresent' => true,  'hwActive' => false, 'pfaPresent' => false, 'pfaActive' => false],
            ['hwPresent' => false, 'hwActive' => true,  'pfaPresent' => false, 'pfaActive' => false],
            ['hwPresent' => true,  'hwActive' => true,  'pfaPresent' => false, 'pfaActive' => false],
            ['hwPresent' => false, 'hwActive' => false, 'pfaPresent' => true,  'pfaActive' => false],
            ['hwPresent' => true,  'hwActive' => false, 'pfaPresent' => true,  'pfaActive' => false],
            ['hwPresent' => false, 'hwActive' => true,  'pfaPresent' => true,  'pfaActive' => false],
            ['hwPresent' => true,  'hwActive' => true,  'pfaPresent' => true,  'pfaActive' => false],
            ['hwPresent' => false, 'hwActive' => false, 'pfaPresent' => false, 'pfaActive' => true],
            ['hwPresent' => true,  'hwActive' => false, 'pfaPresent' => false, 'pfaActive' => true],
            ['hwPresent' => false, 'hwActive' => true,  'pfaPresent' => false, 'pfaActive' => true],
            ['hwPresent' => true,  'hwActive' => true,  'pfaPresent' => false, 'pfaActive' => true],
            ['hwPresent' => false, 'hwActive' => false, 'pfaPresent' => true,  'pfaActive' => true],
            ['hwPresent' => true,  'hwActive' => false, 'pfaPresent' => true,  'pfaActive' => true],
            ['hwPresent' => false, 'hwActive' => true,  'pfaPresent' => true,  'pfaActive' => true],
            ['hwPresent' => true,  'hwActive' => true,  'pfaPresent' => true,  'pfaActive' => true]
        ];
    }

    #[DataProvider('courtOrderCombos')]
    public function testHasHwCourtOrder(bool $hwPresent, bool $hwActive, bool $pfaPresent, bool $pfaActive): void
    {
        $hwCourtOrder = new CourtOrder();
        $hwCourtOrder->setOrderType('hw');
        $hwCourtOrder->setStatus($hwActive ? 'ACTIVE' : 'CLOSED');

        $pfaCourtOrder = new CourtOrder();
        $pfaCourtOrder->setOrderType('pfa');
        $pfaCourtOrder->setStatus($pfaActive ? 'ACTIVE' : 'CLOSED');

        $client = new Client();

        $courtOrders = [];

        if ($hwPresent) {
            $courtOrders[] = $hwCourtOrder;
        }
        if ($pfaPresent) {
            $courtOrders[] = $pfaCourtOrder;
        }

        $client->courtOrders = $courtOrders;

        self::assertEquals($hwPresent && $hwActive, $client->hasHwCourtOrder());
    }

    public function testFindPossibleReplacementsDuals(): void
    {
        $client = new Client();

        // two closed hw court orders for client (doesn't matter if oldest has a report or not)
        $hw1 = $this->createOrder(id: 10, madeDate: '2025-02-02', type: 'hw', status: 'CLOSED');

        $hw2Report = $this->createReport(id: 110, type: '104');
        $hw2 = $this->createOrder(id: 11, madeDate: '2026-02-02', type: 'hw', status: 'CLOSED', reports: [$hw2Report]);

        // active hw court order compatible with latest closed hw court order
        $activeHwReport = $this->createReport(id: 120, type: '104');
        $activeHw = $this->createOrder(id: 12, madeDate: '2026-11-12', type: 'hw', reports: [$activeHwReport]);

        // two closed pfa court orders for client (doesn't matter if oldest has a report or not)
        $pfa1 = $this->createOrder(id: 20, madeDate: '2025-02-02', status: 'CLOSED');

        $pfa2Report = $this->createReport(id: 210);
        $pfa2 = $this->createOrder(id: 21, madeDate: '2026-02-02', status: 'CLOSED', reports: [$pfa2Report]);

        // active pfa court order compatible with latest closed pfa court order
        $activePfaReport = $this->createReport(id: 220);
        $activePfa = $this->createOrder(id: 22, madeDate: '2026-11-11', reports: [$activePfaReport]);

        $client->courtOrders = [$hw1, $hw2, $activeHw, $pfa1, $pfa2, $activePfa];

        $expected = [
            ['replaceReport' => $activeHwReport, 'withReport' => $hw2Report],
            ['replaceReport' => $activePfaReport, 'withReport' => $pfa2Report]
        ];

        self::assertEquals($expected, $client->findPossibleReplacements());
    }

    /**
     * @param int $id
     * @param string $madeDate e.g. '2026-11-11'
     * @param $type 'pfa'|'hw'
     * @param string $status e.g. 'ACTIVE', 'CLOSED'
     * @param array<Report> $reports
     */
    private function createOrder(
        int $id,
        string $madeDate,
        string $type = 'pfa',
        string $status = 'ACTIVE',
        array $reports = []
    ): CourtOrder {
        $courtOrder = new CourtOrder();
        $courtOrder->setId($id);
        $courtOrder->orderMadeDate = new DateTime($madeDate);
        $courtOrder->setOrderType($type);
        $courtOrder->setStatus($status);
        $courtOrder->setReports($reports);
        return $courtOrder;
    }

    /**
     * @param int $id
     * @param string $type e.g. '102', '103', '102-4'
     * @return Report
     */
    private function createReport(int $id, string $type = '102'): Report
    {
        $report = new Report();
        $report->setId($id);
        $report->setType($type);
        $report->setSubmitted(false);
        $report->setUnSubmitDate(null);
        return $report;
    }
}
