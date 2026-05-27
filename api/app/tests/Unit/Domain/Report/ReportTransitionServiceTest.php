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
use OPG\Digideps\Backend\Service\ReportService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ReportTransitionServiceTest extends TestCase
{
    private ReportService&MockObject $mockReportService;
    private ReportTransitionService $sut;

    protected function setUp(): void
    {
        $this->mockReportService = self::createMock(ReportService::class);
        $this->sut = new ReportTransitionService($this->mockReportService);
    }

    /**
     * @param array<CourtOrder> $courtOrders
     */
    private function makeReport(int $id, string $type, array $courtOrders): Report
    {
        $client = new Client();
        $report = new Report($client, $type, new \DateTime('2024-01-01'), new \DateTime('2024-12-31'), false);

        // set the private id via reflection
        $idProp = new \ReflectionProperty(Report::class, 'id');
        $idProp->setValue($report, $id);

        // populate the private courtOrders collection via reflection
        $courtOrdersProp = new \ReflectionProperty(Report::class, 'courtOrders');
        $courtOrdersProp->setValue($report, new ArrayCollection($courtOrders));

        return $report;
    }

    private function makeCourtOrder(CourtOrderType $type, CourtOrderKind $kind, string $uid, Client $client): CourtOrder
    {
        $courtOrder = new CourtOrder();
        $courtOrder->setCourtOrderUid($uid);
        $courtOrder->setOrderType($type);
        $courtOrder->setOrderKind($kind);
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new \DateTime('2020-01-01'));
        $courtOrder->setOrderReportType(
            $type === CourtOrderType::PFA ? CourtOrderReportType::OPG102 : CourtOrderReportType::OPG104
        );

        $courtOrder->setClient($client);
        $client->addCourtOrder($courtOrder);

        return $courtOrder;
    }

    /* HYBRID TO DUAL TESTS */

    public function testHybridToDualSplitsHybridReportIntoTwo(): void
    {
        // create the hybrid report
        $client = new Client();
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, CourtOrderKind::Hybrid, '100010203', $client);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, CourtOrderKind::Hybrid, '100010204', $client);

        $hybridReport = $this->makeReport(42, Report::LAY_COMBINED_HIGH_ASSETS_TYPE, [$pfaCourtOrder, $hwCourtOrder]);

        // both court orders must reference the hybrid report as their latest report
        $pfaCourtOrder->addReport($hybridReport);
        $hwCourtOrder->addReport($hybridReport);

        $newHwReport = $this->makeReport(99, Report::LAY_HW_TYPE, []);

        $this->mockReportService->expects($this->once())
            ->method('createReportFromOrder')
            ->with($hwCourtOrder)
            ->willReturn($newHwReport);

        $oldReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Hybrid,
            DeputyType::LAY
        );

        $newReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Dual,
            DeputyType::LAY
        );

        // act
        $result = $this->sut->transitionReport($hybridReport, $oldReportType, $newReportType);

        // assert
        self::assertNotNull($result);
        self::assertTrue($result->transitioned);
        self::assertEmpty($result->errorMessages);

        // the original hybrid report and the new HW report should both be in updatedReports
        self::assertContains($hybridReport, $result->updatedReports);
        self::assertContains($newHwReport, $result->updatedReports);

        // both court orders should be in updatedCourtOrders
        self::assertContains($pfaCourtOrder, $result->updatedCourtOrders);
        self::assertContains($hwCourtOrder, $result->updatedCourtOrders);

        // the HW court order should now reference the new report (not the old hybrid one)
        self::assertTrue($hwCourtOrder->getReports()->contains($newHwReport));
        self::assertFalse($hwCourtOrder->getReports()->contains($hybridReport));
    }

    public function testHybridToDualReturnsErrorWhenCourtOrderPairIsInvalid(): void
    {
        // only one court order on the report -> flagged as invalid
        $client = new Client();
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, CourtOrderKind::Hybrid, 'pfa-uid-002', $client);
        $hybridReport = $this->makeReport(43, Report::LAY_COMBINED_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);
        $pfaCourtOrder->addReport($hybridReport);

        $oldReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Hybrid,
            DeputyType::LAY
        );

        $newReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Dual,
            DeputyType::LAY
        );

        $result = $this->sut->transitionReport($hybridReport, $oldReportType, $newReportType);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
        self::assertNotEmpty($result->errorMessages);
    }

    public function testHybridToDualReturnsErrorWhenHybridReportIsNotLatestOnBothOrders(): void
    {
        $client = new Client();
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, CourtOrderKind::Hybrid, 'pfa-uid-003', $client);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, CourtOrderKind::Hybrid, 'hw-uid-003', $client);

        $hybridReport = $this->makeReport(44, Report::LAY_COMBINED_HIGH_ASSETS_TYPE, [$pfaCourtOrder, $hwCourtOrder]);

        // only add the hybrid report to the PFA court order; the HW court order gets a *different* report
        $pfaCourtOrder->addReport($hybridReport);

        $otherReport = $this->makeReport(45, Report::LAY_HW_TYPE, []);
        $hwCourtOrder->addReport($otherReport);

        $oldReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Hybrid,
            DeputyType::LAY
        );

        $newReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Dual,
            DeputyType::LAY
        );

        $result = $this->sut->transitionReport($hybridReport, $oldReportType, $newReportType);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
        self::assertNotEmpty($result->errorMessages);
    }

    /* DUAL TO HYBRID TESTS */

    /**
     * @return array{ReportType, ReportType}
     */
    private function makeDualToHybridReportTypes(): array
    {
        $oldReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Dual,
            DeputyType::LAY
        );

        $newReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Hybrid,
            DeputyType::LAY
        );

        return [$oldReportType, $newReportType];
    }

    public function testDualToHybridMergesHwReportIntoPfaReport(): void
    {
        // two separate dual court orders sharing a client
        $client = new Client();
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, CourtOrderKind::Dual, '200010001', $client);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, CourtOrderKind::Dual, '200010002', $client);

        $client->setCourtOrders(new ArrayCollection([$pfaCourtOrder, $hwCourtOrder]));

        // PFA report is the one being transitioned (id=10); the HW report will be merged away (id=11)
        $pfaReport = $this->makeReport(10, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);
        $hwReport  = $this->makeReport(11, Report::LAY_HW_TYPE, [$hwCourtOrder]);

        $pfaCourtOrder->addReport($pfaReport);
        $hwCourtOrder->addReport($hwReport);

        [$oldReportType, $newReportType] = $this->makeDualToHybridReportTypes();

        // act: transition the PFA report from dual to hybrid
        $result = $this->sut->transitionReport($pfaReport, $oldReportType, $newReportType);

        // assert
        self::assertNotNull($result);
        self::assertTrue($result->transitioned);
        self::assertEmpty($result->errorMessages);

        // PFA report becomes the hybrid report; HW report is listed as removed
        self::assertContains($pfaReport, $result->updatedReports);
        self::assertContains($hwReport, $result->removedReports);

        // both court orders should be updated
        self::assertContains($pfaCourtOrder, $result->updatedCourtOrders);
        self::assertContains($hwCourtOrder, $result->updatedCourtOrders);

        // the HW court order should now reference the merged (PFA/hybrid) report, not the old HW report
        $hwReports = $hwCourtOrder->getReports();
        self::assertTrue($hwReports->contains($pfaReport));
        self::assertFalse($hwReports->contains($hwReport));
    }

    public function testDualToHybridReturnsErrorWhenReportHasNoCourtOrders(): void
    {
        // a report with no court orders -> can't find a client -> error
        $orphanReport = $this->makeReport(20, Report::LAY_PFA_HIGH_ASSETS_TYPE, []);

        [$oldReportType, $newReportType] = $this->makeDualToHybridReportTypes();

        $result = $this->sut->transitionReport($orphanReport, $oldReportType, $newReportType);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
        self::assertNotEmpty($result->errorMessages);
    }

    public function testDualToHybridReturnsErrorWhenClientHasInvalidCourtOrderPair(): void
    {
        // client has two PFA orders (no HW) -> invalid
        $client = new Client();
        $pfaCourtOrder1 = $this->makeCourtOrder(CourtOrderType::PFA, CourtOrderKind::Dual, '300010001', $client);
        $pfaCourtOrder2 = $this->makeCourtOrder(CourtOrderType::PFA, CourtOrderKind::Dual, '300010002', $client);

        $client->setCourtOrders(new ArrayCollection([$pfaCourtOrder1, $pfaCourtOrder2]));

        $pfaReport = $this->makeReport(30, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder1]);
        $pfaCourtOrder1->addReport($pfaReport);


        [$oldReportType, $newReportType] = $this->makeDualToHybridReportTypes();

        $result = $this->sut->transitionReport($pfaReport, $oldReportType, $newReportType);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
        self::assertNotEmpty($result->errorMessages);
    }

    public function testDualToHybridReturnsErrorWhenChangedReportIsNotLatestOnEitherOrder(): void
    {
        // the transitioning report (id=40) is NOT the latest on either court order
        $client = new Client();
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, CourtOrderKind::Dual, '400010001', $client);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, CourtOrderKind::Dual, '400010002', $client);

        $client->setCourtOrders(new ArrayCollection([$pfaCourtOrder, $hwCourtOrder]));

        // transitioning report is linked to pfaCourtOrder but is NOT its latest
        $transitioningReport = $this->makeReport(40, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);

        // latest on PFA is a *different* report (id=41, started later)
        $latestPfaReport = $this->makeReport(41, Report::LAY_PFA_HIGH_ASSETS_TYPE, []);
        $pfaCourtOrder->addReport($latestPfaReport);

        // latest on HW is also a different report (id=42)
        $latestHwReport = $this->makeReport(42, Report::LAY_HW_TYPE, []);
        $hwCourtOrder->addReport($latestHwReport);

        [$oldReportType, $newReportType] = $this->makeDualToHybridReportTypes();

        $result = $this->sut->transitionReport($transitioningReport, $oldReportType, $newReportType);

        self::assertNotNull($result);
        self::assertFalse($result->transitioned);
        self::assertNotEmpty($result->errorMessages);
    }

    /* SINGLE TO DUAL TESTS */

    public function testSingleToDual(): void
    {
        $client = new Client();
        $pfaCourtOrder = $this->makeCourtOrder(CourtOrderType::PFA, CourtOrderKind::Single, '400030001', $client);
        $hwCourtOrder = $this->makeCourtOrder(CourtOrderType::HW, CourtOrderKind::Single, '400020002', $client);

        $client->setCourtOrders(new ArrayCollection([$pfaCourtOrder, $hwCourtOrder]));

        // add a report to one of the single court orders
        $transitioningReport = $this->makeReport(87, Report::LAY_PFA_HIGH_ASSETS_TYPE, [$pfaCourtOrder]);
        $pfaCourtOrder->addReport($transitioningReport);

        $oldReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Single,
            DeputyType::LAY
        );

        $newReportType = new ReportType(
            CourtOrderReportType::OPG102,
            CourtOrderType::PFA,
            CourtOrderKind::Dual,
            DeputyType::LAY
        );

        // mock creation of the new report
        $newHwReport = $this->makeReport(72, Report::LAY_HW_TYPE, []);

        $this->mockReportService->expects($this->once())
            ->method('createReportFromOrder')
            ->with($hwCourtOrder)
            ->willReturn($newHwReport);

        // act: transition the single to a dual
        $this->sut->transitionReport($transitioningReport, $oldReportType, $newReportType);

        // assert: the hw court order is marked as a dual, has a new report attached, and has the pfa court order
        // as a sibling
        self::assertEquals(CourtOrderKind::Dual, $hwCourtOrder->getOrderKind());
        self::assertEquals($newHwReport, $hwCourtOrder->getLatestReport());
        self::assertEquals(Report::LAY_HW_TYPE, $hwCourtOrder->getLatestReport()->getType());
        self::assertEquals($pfaCourtOrder, $hwCourtOrder->getSibling());

        // assert: the pfa court order is marked as a dual, has been updated to the new report type,
        // and has the hw court order as a sibling
        self::assertEquals(CourtOrderKind::Dual, $pfaCourtOrder->getOrderKind());
        self::assertEquals($newReportType->courtOrderReportType, $pfaCourtOrder->getOrderReportType());
        self::assertEquals(Report::LAY_PFA_HIGH_ASSETS_TYPE, $pfaCourtOrder->getLatestReport()->getType());
        self::assertEquals($hwCourtOrder, $pfaCourtOrder->getSibling());
    }
}
