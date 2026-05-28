<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Domain\Report;

use Doctrine\Common\Collections\ArrayCollection;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Domain\Report\ReportTransitionService;
use OPG\Digideps\Backend\Domain\Report\ReportType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\CourtOrderRepository;
use OPG\Digideps\Backend\Service\ReportService;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipChange;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ReportTransitionServiceTest extends TestCase
{
    private ReportService&MockObject $mockReportService;
    private CourtOrderRepository&MockObject $mockCourtOrderRepository;
    private ReportTransitionService $sut;

    protected function setUp(): void
    {
        $this->mockReportService = self::createMock(ReportService::class);
        $this->mockCourtOrderRepository = self::createMock(CourtOrderRepository::class);
        $this->sut = new ReportTransitionService($this->mockReportService, $this->mockCourtOrderRepository);
    }

    /**
     * @param array<CourtOrder> $courtOrders
     */
    private function makeReport(int $id, string $type, array $courtOrders, ?\DateTime $startDate = null): Report
    {
        if ($startDate === null) {
            $startDate = new \DateTime();
        }
        $endDate = $startDate->add(new \DateInterval('P364D'));

        $client = new Client();
        $report = new Report($client, $type, $startDate, $endDate, false);

        // set the private id via reflection
        $idProp = new \ReflectionProperty(Report::class, 'id');
        $idProp->setValue($report, $id);

        // populate the private courtOrders collection via reflection
        $courtOrdersProp = new \ReflectionProperty(Report::class, 'courtOrders');
        $courtOrdersProp->setValue($report, new ArrayCollection($courtOrders));

        foreach ($courtOrders as $courtOrder) {
            $courtOrder->addReport($report);
        }

        return $report;
    }

    private function makeCourtOrder(
        CourtOrderType $type,
        string $uid,
        ?CourtOrderKind $kind = CourtOrderKind::Single
    ): CourtOrder {
        $courtOrder = new CourtOrder();
        $courtOrder->setCourtOrderUid($uid);
        $courtOrder->setOrderType($type);
        $courtOrder->setOrderKind($kind);
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new \DateTime('2020-01-01'));
        $courtOrder->setOrderReportType(
            $type === CourtOrderType::PFA ? CourtOrderReportType::OPG102 : CourtOrderReportType::OPG104
        );
        return $courtOrder;
    }

    /* SINGLE TO DUAL TESTS */

    public function testSingleToDualPersistsExistingReport(): void
    {
        // pfa pre-exists
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, '400030001', CourtOrderKind::Dual);

        // hw court order is a new sibling of pfa but not fully processed yet
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, '400020002');
        $pfaCourtOrder->setSibling($hwCourtOrder);

        // add a report to the single court order
        $pfaReport = $this->makeReport(62, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);

        // mock creation of the new report (expect it to be added to the hw report)
        $newHwReport = $this->makeReport(63, Report::LAY_HW_TYPE, []);

        $this->mockReportService->expects($this->once())
            ->method('createReportFromOrder')
            ->with($hwCourtOrder)
            ->willReturn($newHwReport);

        $courtOrderRelationshipChange = new CourtOrderRelationshipChange(
            courtOrder: $pfaCourtOrder,
            oldKind: CourtOrderKind::Single,
            oldSiblingId: null
        );

        // act: transition the single to a dual
        $this->sut->transitionReports($courtOrderRelationshipChange);

        // assert: the hw court order has the new report attached
        $actualHwReport = $hwCourtOrder->getLatestReport();
        self::assertEquals($newHwReport, $actualHwReport);
        self::assertEquals(Report::LAY_HW_TYPE, $actualHwReport->getType());

        // assert: the pfa court order's report has been retained
        $actualPfaReport = $pfaCourtOrder->getLatestReport();
        self::assertEquals($pfaReport, $actualPfaReport);
        self::assertEquals(Report::LAY_PFA_HIGH_ASSETS_TYPE, $actualPfaReport->getType());
    }
}
