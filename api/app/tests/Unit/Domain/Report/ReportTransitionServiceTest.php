<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Domain\Report;

use Doctrine\Common\Collections\ArrayCollection;
use OPG\Digideps\Backend\Domain\Report\ReportTransitionService;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\CourtOrderRepository;
use OPG\Digideps\Backend\Service\ReportService;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipChange;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
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

        $idProp = new \ReflectionProperty(Report::class, 'id');
        $idProp->setValue($report, $id);

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
        ?CourtOrderKind $kind = null,
        ?CourtOrderReportType $orderReportType = null,
    ): CourtOrder {
        $kind ??= CourtOrderKind::Single;
        $orderReportType ??= ($type === CourtOrderType::PFA ? CourtOrderReportType::OPG102 : CourtOrderReportType::OPG104);

        $courtOrder = new CourtOrder(
            "{$id}0011",
            $type,
            $orderReportType,
            $kind,
            new \DateTime('2020-01-01'),
            new Client()
        );

        $courtOrder->setOrderReportType($orderReportType);

        $idProp = new \ReflectionProperty(CourtOrder::class, 'id');
        $idProp->setValue($courtOrder, $id);

        return $courtOrder;
    }

    /**
     * Set up the court order repository find() mock using a callback so that argument matching
     * is reliable regardless of PHPUnit willReturnMap quirks.
     *
     * @param array<int, CourtOrder|null> $idMap  keys are court order IDs, values are the objects to return
     */
    private function mockFind(array $idMap, int $expectedCallCount): void
    {
        $this->mockCourtOrderRepository->expects(self::exactly($expectedCallCount))
            ->method('find')
            ->willReturnCallback(fn (int $id) => $idMap[$id] ?? null);
    }

    /* HYBRID TO DUAL */

    public function testHybridToDual(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 10, CourtOrderKind::Dual);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 11, CourtOrderKind::Dual);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        $hybridReport = $this->makeReport(12, Report::LAY_COMBINED_HIGH_ASSETS_TYPE, [$pfaCourtOrder, $hwCourtOrder]);

        // find(courtOrderId=10), find(currentSiblingId=11) = 2 calls; oldSiblingId==currentSiblingId so no extra find
        $this->mockFind([10 => $pfaCourtOrder, 11 => $hwCourtOrder], 2);

        $newHwReport = $this->makeReport(13, Report::LAY_HW_TYPE, []);

        $this->mockReportService->expects(self::once())
            ->method('createReportFromOrder')
            ->with($hwCourtOrder)
            ->willReturn($newHwReport);

        $courtOrderRelationshipChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Hybrid,
            oldSiblingId: $hwCourtOrder->getId(),
        );

        $result = $this->sut->transitionReports($courtOrderRelationshipChange);

        self::assertNotNull($result);
        self::assertEmpty($result->errorMessages);

        self::assertEquals($hybridReport, $pfaCourtOrder->getLatestReport());
        self::assertEquals(Report::LAY_PFA_HIGH_ASSETS_TYPE, $pfaCourtOrder->getLatestReport()?->getType());

        self::assertEquals($newHwReport, $hwCourtOrder->getLatestReport());
        self::assertEquals(Report::LAY_HW_TYPE, $hwCourtOrder->getLatestReport()?->getType());

        self::assertNotContains($hybridReport, $hwCourtOrder->getReports());
    }

    /* DUAL TO HYBRID */

    public function testDualToHybrid(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 40, CourtOrderKind::Hybrid, CourtOrderReportType::OPG102);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 41);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        $pfaReport = $this->makeReport(42, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);
        $hwReport = $this->makeReport(43, Report::LAY_HW_TYPE, [$hwCourtOrder]);

        // find(courtOrderId=40), find(currentSiblingId=41) = 2 calls; oldSiblingId==currentSiblingId so no extra find
        $this->mockFind([40 => $pfaCourtOrder, 41 => $hwCourtOrder], 2);

        $courtOrderRelationshipChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Dual,
            oldSiblingId: $hwCourtOrder->getId(),
        );

        $result = $this->sut->transitionReports($courtOrderRelationshipChange);

        self::assertNotNull($result);
        self::assertEmpty($result->errorMessages);

        self::assertEquals($pfaReport, $pfaCourtOrder->getLatestReport());
        self::assertEquals($pfaReport, $hwCourtOrder->getLatestReport());

        self::assertEquals(Report::LAY_COMBINED_HIGH_ASSETS_TYPE, $pfaCourtOrder->getLatestReport()?->getType());
        self::assertEquals(Report::LAY_COMBINED_HIGH_ASSETS_TYPE, $hwCourtOrder->getLatestReport()?->getType());

        self::assertNotContains($hwReport, $hwCourtOrder->getReports());
    }

    /* SINGLE TO DUAL */

    public function testSingleToDualPersistsExistingReport(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 50, CourtOrderKind::Dual);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 52);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        $pfaReport = $this->makeReport(62, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);
        $newHwReport = $this->makeReport(63, Report::LAY_HW_TYPE, []);

        // find(courtOrderId=50), find(currentSiblingId=52) = 2 calls (no oldSiblingId for single->dual)
        $this->mockFind([50 => $pfaCourtOrder, 52 => $hwCourtOrder], 2);

        $this->mockReportService->expects($this->once())
            ->method('createReportFromOrder')
            ->with($hwCourtOrder)
            ->willReturn($newHwReport);

        $courtOrderRelationshipChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Single,
            oldSiblingId: null
        );

        $this->sut->transitionReports($courtOrderRelationshipChange);

        $actualHwReport = $hwCourtOrder->getLatestReport();
        self::assertEquals($newHwReport, $actualHwReport);
        self::assertEquals(Report::LAY_HW_TYPE, $actualHwReport?->getType());

        $actualPfaReport = $pfaCourtOrder->getLatestReport();
        self::assertEquals($pfaReport, $actualPfaReport);
        self::assertEquals(Report::LAY_PFA_HIGH_ASSETS_TYPE, $actualPfaReport?->getType());
    }

    /* ERRORS BEFORE TRANSITION STARTS */

    public function testTransitionReturnsNullWhenNoKindOrSiblingChange(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 70, CourtOrderKind::Dual);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 71, CourtOrderKind::Dual);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Dual,
            oldSiblingId: $hwCourtOrder->getId(),
        );

        $this->mockCourtOrderRepository->expects(self::never())->method('find');

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNull($result);
    }

    public function testTransitionReturnsErrorWhenCourtOrderPairInvalid(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 80, CourtOrderKind::Dual);

        // find(courtOrderId=80), find(currentSiblingId=999 -> null) = 2 calls; null sibling makes pair invalid
        $this->mockFind([80 => $pfaCourtOrder], 2);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: 999,
            oldKind: CourtOrderKind::Hybrid,
            oldSiblingId: null,
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
        self::assertNotEmpty($result->errorMessages);
    }

    /* HYBRID TO DUAL ERRORS */

    public function testHybridToDualReturnsErrorWhenOldSiblingIdNull(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 90, CourtOrderKind::Hybrid);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 91, CourtOrderKind::Dual);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        $this->makeReport(92, Report::LAY_COMBINED_HIGH_ASSETS_TYPE, [$pfaCourtOrder, $hwCourtOrder]);

        // find(courtOrderId=90), find(currentSiblingId=91) = 2 calls; oldSiblingId=null so getOldSibling returns early
        $this->mockFind([90 => $pfaCourtOrder, 91 => $hwCourtOrder], 2);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Hybrid,
            oldSiblingId: null,
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
    }

    public function testHybridToDualReturnsErrorWhenOldSiblingNotFound(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 100, CourtOrderKind::Dual);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 101, CourtOrderKind::Hybrid);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        $this->makeReport(102, Report::LAY_COMBINED_HIGH_ASSETS_TYPE, [$pfaCourtOrder, $hwCourtOrder]);

        // find(100), find(101), find(999 -> null) = 3 calls
        $this->mockFind([100 => $pfaCourtOrder, 101 => $hwCourtOrder], 3);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Hybrid,
            oldSiblingId: 999,
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
    }

    public function testHybridToDualReturnsErrorWhenPersistingReportNotFound(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 110, CourtOrderKind::Dual);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 111, CourtOrderKind::Hybrid);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        // no report on pfaCourtOrder
        // find(110), find(111); oldSiblingId==currentSiblingId so no extra find = 2 calls
        $this->mockFind([110 => $pfaCourtOrder, 111 => $hwCourtOrder], 2);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Hybrid,
            oldSiblingId: $hwCourtOrder->getId(),
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
        self::assertStringContainsString('Could not find existing hybrid report', implode('', $result->errorMessages));
    }

    /* DUAL TO HYBRID ERRORS */

    public function testDualToHybridReturnsErrorWhenOldSiblingIdNull(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 120, CourtOrderKind::Hybrid);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 121);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        $this->makeReport(122, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);
        $this->makeReport(123, Report::LAY_HW_TYPE, [$hwCourtOrder]);

        // find(120), find(121) = 2 calls; oldSiblingId=null so getOldSibling returns early
        $this->mockFind([120 => $pfaCourtOrder, 121 => $hwCourtOrder], 2);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Dual,
            oldSiblingId: null,
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
        self::assertStringContainsString(
            'Expected old sibling ID to be present for hybrid to dual transition',
            implode('', $result->errorMessages)
        );
    }

    public function testDualToHybridReturnsErrorWhenBothReportsUnavailable(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 130, CourtOrderKind::Hybrid);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 131);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        // neither court order has a report
        // find(130), find(131); oldSiblingId==currentSiblingId so no extra find = 2 calls
        $this->mockFind([130 => $pfaCourtOrder, 131 => $hwCourtOrder], 2);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Dual,
            oldSiblingId: $hwCourtOrder->getId(),
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
        self::assertStringContainsString('Persisting and/or defunct report unavailable', implode('', $result->errorMessages));
    }

    /* SINGLE TO DUAL ERRORS */

    public function testSingleToDualReturnsErrorWhenNeitherCourtOrderHasReport(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 140);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 141);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        // find(140), find(141) = 2 calls
        $this->mockFind([140 => $pfaCourtOrder, 141 => $hwCourtOrder], 2);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Single,
            oldSiblingId: null,
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
    }

    public function testSingleToDualReturnsErrorWhenBothCourtOrdersHaveReports(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 150);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 151);
        $pfaCourtOrder->setSibling($hwCourtOrder);

        $this->makeReport(152, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);
        $this->makeReport(153, Report::LAY_HW_TYPE, [$hwCourtOrder]);

        // find(150), find(151) = 2 calls
        $this->mockFind([150 => $pfaCourtOrder, 151 => $hwCourtOrder], 2);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $hwCourtOrder->getId(),
            oldKind: CourtOrderKind::Single,
            oldSiblingId: null,
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
    }

    /* SIBLING ID CHANGE */

    public function testDualToHybridWithSiblingIdChange(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 160, CourtOrderKind::Hybrid);
        $newHwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 161, CourtOrderKind::Hybrid);
        $pfaCourtOrder->setSibling($newHwCourtOrder);
        $oldHwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 162, CourtOrderKind::Dual);

        $pfaReport = $this->makeReport(163, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);
        $oldHwReport = $this->makeReport(164, Report::LAY_HW_TYPE, [$oldHwCourtOrder]);

        // find(160), find(161), find(162 for oldSibling) = 3 calls
        $this->mockFind([160 => $pfaCourtOrder, 161 => $newHwCourtOrder, 162 => $oldHwCourtOrder], 3);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $newHwCourtOrder->getId(),
            oldKind: CourtOrderKind::Dual,
            oldSiblingId: $oldHwCourtOrder->getId(),
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertTrue($result->transitioned);
        self::assertEmpty($result->errorMessages);

        // defunct report remains on the old sibling
        self::assertContains($oldHwReport, $oldHwCourtOrder->getReports());

        self::assertEquals($pfaReport, $newHwCourtOrder->getLatestReport());
    }

    public function testHybridToDualWithSiblingIdChange(): void
    {
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, 170, CourtOrderKind::Dual);
        $newHwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 171, CourtOrderKind::Dual);
        $pfaCourtOrder->setSibling($newHwCourtOrder);
        $oldHwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, 172, CourtOrderKind::Hybrid);

        $hybridReport = $this->makeReport(173, Report::LAY_COMBINED_HIGH_ASSETS_TYPE, [$pfaCourtOrder, $oldHwCourtOrder]);
        $newHwReport = $this->makeReport(174, Report::LAY_HW_TYPE, []);

        // find(170), find(171), find(172 for oldSibling) = 3 calls
        $this->mockFind([170 => $pfaCourtOrder, 171 => $newHwCourtOrder, 172 => $oldHwCourtOrder], 3);

        $this->mockReportService->expects(self::once())
            ->method('createReportFromOrder')
            ->with($newHwCourtOrder)
            ->willReturn($newHwReport);

        $courtOrderChange = new CourtOrderRelationshipChange(
            courtOrderId: $pfaCourtOrder->getId(),
            currentKind: $pfaCourtOrder->getOrderKind(),
            currentSiblingId: $newHwCourtOrder->getId(),
            oldKind: CourtOrderKind::Hybrid,
            oldSiblingId: $oldHwCourtOrder->getId(),
        );

        $result = $this->sut->transitionReports($courtOrderChange);

        self::assertNotNull($result);
        self::assertTrue($result->transitioned);
        self::assertEmpty($result->errorMessages);

        self::assertNotContains($hybridReport, $oldHwCourtOrder->getReports());
        self::assertEquals(Report::LAY_PFA_HIGH_ASSETS_TYPE, $pfaCourtOrder->getLatestReport()?->getType());
        self::assertEquals($newHwReport, $newHwCourtOrder->getLatestReport());
    }
}
