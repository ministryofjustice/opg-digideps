<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Domain\Report;

use Doctrine\Common\Collections\ArrayCollection;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Domain\Report\ReportTransitionService;
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
    private function makeReport(int $id, string $type, array $courtOrders): Report
    {
        $startDate = new \DateTime();
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
        int $id,
        ?CourtOrderKind $kind = CourtOrderKind::Single,
        ?CourtOrderReportType $orderReportType = null,
    ): CourtOrder {
        $courtOrder = new CourtOrder();
        $courtOrder->setCourtOrderUid("{$id}0011");
        $courtOrder->setOrderType($type);
        $courtOrder->setOrderKind($kind);
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new \DateTime('2020-01-01'));

        if ($orderReportType === null) {
            $orderReportType = ($type === CourtOrderType::PFA ? CourtOrderReportType::OPG102 : CourtOrderReportType::OPG104);
        }

        $courtOrder->setOrderReportType($orderReportType);

        $idProp = new \ReflectionProperty(CourtOrder::class, 'id');
        $idProp->setValue($courtOrder, $id);

        return $courtOrder;
    }

    /* HYBRID TO DUAL TESTS */

    public function testHybridToDual(): void
    {
        // pfa and hw both pre-exist; hw is sibling of the pfa
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 10, CourtOrderKind::Dual);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 11, CourtOrderKind::Dual);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        // hw and pfa have a single (hybrid) report before the transition
        $hybridReport = $this->makeReport(12, Report::LAY_COMBINED_HIGH_ASSETS_TYPE, [$pfaCourtOrder, $hwCourtOrder]);

        // old hybrid sibling is the hw court order
        $this->mockCourtOrderRepository->expects(self::once())
            ->method('find')
            ->with($hwCourtOrder->getId())
            ->willReturn($hwCourtOrder);

        // new report is created for the hw as it splits to a dual
        $newHwReport = $this->makeReport(13, Report::LAY_HW_TYPE, []);

        $this->mockReportService->expects(self::once())
            ->method('createReportFromOrder')
            ->with($hwCourtOrder)
            ->willReturn($newHwReport);

        // hw and pfa remain paired but transition from hybrid to dual
        $courtOrderRelationshipChange = new CourtOrderRelationshipChange(
            courtOrder: $pfaCourtOrder,
            oldKind: CourtOrderKind::Hybrid,
            oldSiblingId: $hwCourtOrder->getId(),
        );

        // act: transition the hybrid to a dual
        $result = $this->sut->transitionReports($courtOrderRelationshipChange);

        // assert: the transition was carried out without errors
        self::assertNotNull($result);
        self::assertEmpty($result->errorMessages);

        // assert: the hybrid report has been retained as the report on the pfa and has had its type reset
        self::assertEquals($hybridReport, $pfaCourtOrder->getLatestReport());
        self::assertEquals(Report::LAY_PFA_HIGH_ASSETS_TYPE, $pfaCourtOrder->getLatestReport()->getType());

        // assert: the newly-created report has been set as the latest report on the hw
        self::assertEquals($newHwReport, $hwCourtOrder->getLatestReport());
        self::assertEquals(Report::LAY_HW_TYPE, $hwCourtOrder->getLatestReport()->getType());

        // assert: the hybrid report is no longer associated with the hw
        self::assertNotContains($hybridReport, $hwCourtOrder->getReports());
    }

    /* DUAL TO HYBRID TESTS */

    public function testDualToHybrid(): void
    {
        // pfa and hw both pre-exist; hw is sibling of the pfa
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 40, CourtOrderKind::Hybrid, CourtOrderReportType::OPG102);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 41);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        // hw and pfa have separate (dual) reports
        $pfaReport = $this->makeReport(42, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);
        $hwReport = $this->makeReport(43, Report::LAY_HW_TYPE, [$hwCourtOrder]);

        // old dual sibling is the hw court order
        $this->mockCourtOrderRepository->expects(self::once())
            ->method('find')
            ->with($hwCourtOrder->getId())
            ->willReturn($hwCourtOrder);

        // hw and pfa remain paired but migrate to hybrid from dual
        $courtOrderRelationshipChange = new CourtOrderRelationshipChange(
            courtOrder: $pfaCourtOrder,
            oldKind: CourtOrderKind::Dual,
            oldSiblingId: $hwCourtOrder->getId(),
        );

        // act: transition the dual to a hybrid
        $result = $this->sut->transitionReports($courtOrderRelationshipChange);

        // assert: the transition was carried out without errors
        self::assertNotNull($result);
        self::assertEmpty($result->errorMessages);

        // assert: the pfa report is retained as the hybrid
        self::assertEquals($pfaReport, $pfaCourtOrder->getLatestReport());
        self::assertEquals($pfaReport, $hwCourtOrder->getLatestReport());

        // assert: the old dual report has had its type reset to hybrid
        self::assertEquals(Report::LAY_COMBINED_HIGH_ASSETS_TYPE, $pfaCourtOrder->getLatestReport()->getType());
        self::assertEquals(Report::LAY_COMBINED_HIGH_ASSETS_TYPE, $hwCourtOrder->getLatestReport()->getType());

        // assert: the hw report has been removed from the hw court order
        self::assertNotContains($hwReport, $hwCourtOrder->getReports());
    }

    /* SINGLE TO DUAL TESTS */

    public function testSingleToDualPersistsExistingReport(): void
    {
        // pfa pre-exists
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 50, CourtOrderKind::Dual);

        // hw court order is a new sibling of pfa but not fully processed yet
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 52);
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
