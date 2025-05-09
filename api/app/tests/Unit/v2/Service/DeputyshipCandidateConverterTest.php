<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Entity\Deputy;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\StagingSelectedCandidate;
use App\Repository\CourtOrderRepository;
use App\Repository\DeputyRepository;
use App\Repository\NdrRepository;
use App\Repository\ReportRepository;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeputyshipCandidateConverterTest extends TestCase
{
    private CourtOrderRepository&MockObject $mockCourtOrderRepository;
    private DeputyRepository&MockObject $mockDeputyRepository;
    private ReportRepository&MockObject $mockReportRepository;
    private NdrRepository&MockObject $mockNdrRepository;
    private DeputyshipCandidateConverter $sut;

    public function setUp(): void
    {
        $this->mockCourtOrderRepository = $this->createMock(CourtOrderRepository::class);
        $this->mockDeputyRepository = $this->createMock(DeputyRepository::class);
        $this->mockReportRepository = $this->createMock(ReportRepository::class);
        $this->mockNdrRepository = $this->createMock(NdrRepository::class);

        $this->sut = new DeputyshipCandidateConverter(
            $this->mockCourtOrderRepository,
            $this->mockDeputyRepository,
            $this->mockReportRepository,
            $this->mockNdrRepository,
        );
    }

    private function setupMockCourtOrderRepository(?CourtOrder $courtOrderToReturn): void
    {
        $this->mockCourtOrderRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['courtOrderUid' => '1'])
            ->willReturn($courtOrderToReturn);
    }

    public function testCreateEntitiesFromCandidatesMultipleOrderUidsFail(): void
    {
        $candidate1 = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrder, '1');
        $candidate2 = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrder, '2');
        $candidates = [$candidate1, $candidate2];

        $result = $this->sut->createEntitiesFromCandidates($candidates);
        $errors = $result->getErrors();

        self::assertEquals([], $result->getEntities());
        self::assertCount(1, $errors);
        self::assertMatchesRegularExpression('/.*more than one order UID.*/', $errors[0]);
    }

    public function testCreateEntitiesFromCandidatesMissingValuesFail(): void
    {
        $candidate1 = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrder, '1');
        $candidates = [$candidate1];

        $result = $this->sut->createEntitiesFromCandidates($candidates);
        $errors = $result->getErrors();

        self::assertEquals([], $result->getEntities());
        self::assertCount(1, $errors);
        self::assertMatchesRegularExpression('/.*court order could not be created.*/', $errors[0]);
    }

    public function testCreateEntitiesFromCandidatesNoOrderInsertOrOrderFoundFail(): void
    {
        $candidate1 = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateOrderStatus, '1');
        $candidates = [$candidate1];

        $this->setupMockCourtOrderRepository(null);

        $result = $this->sut->createEntitiesFromCandidates($candidates);
        $errors = $result->getErrors();

        self::assertEquals([], $result->getEntities());

        self::assertCount(1, $errors);
        self::assertMatchesRegularExpression('/.*non-existent court order with UID 1.*/', $errors[0]);
    }

    public function testCreateEntitiesFromCandidatesInsertOrderSuccess(): void
    {
        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrder, '1');
        $candidate->orderType = 'pfa';
        $candidate->status = 'ACTIVE';
        $candidate->orderMadeDate = '2018-01-21';

        $result = $this->sut->createEntitiesFromCandidates([$candidate]);

        // expect there to be one court order entity created
        /** @var CourtOrder $courtOrder */
        $courtOrder = $result->getEntities()[0];

        self::assertEquals('ACTIVE', $courtOrder->getStatus());
        self::assertEquals('1', $courtOrder->getCourtOrderUid());
        self::assertEquals(new \DateTime('2018-01-21'), $courtOrder->getOrderMadeDate());
        self::assertEquals('pfa', $courtOrder->getOrderType());
    }

    public function testCreateEntitiesFromCandidatesUpdateOrderStatusSuccess(): void
    {
        $courtOrder = new CourtOrder();
        $this->setupMockCourtOrderRepository($courtOrder);

        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateOrderStatus, '1');
        $candidate->status = 'CLOSED';

        $result = $this->sut->createEntitiesFromCandidates([$candidate]);

        // expect there to be one court order entity created, and it should be the first item in the list;
        // as there was an update to its status, it should be set to the value from the update candidate
        /** @var CourtOrder $courtOrderToSave */
        $courtOrderToSave = $result->getEntities()[0];

        self::assertEquals($courtOrder, $courtOrderToSave);
        self::assertEquals('CLOSED', $courtOrder->getStatus());
    }

    public function testCreateEntitiesFromCandidatesInsertOrderDeputySuccess(): void
    {
        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderDeputy, '1');
        $candidate->deputyId = 2;
        $candidate->deputyStatusOnOrder = true;

        $courtOrder = new CourtOrder();
        $this->setupMockCourtOrderRepository($courtOrder);

        $deputy = new Deputy();

        $this->mockDeputyRepository->expects($this->once())
            ->method('find')
            ->willReturn($deputy);

        // test
        $result = $this->sut->createEntitiesFromCandidates([$candidate]);
        $entities = $result->getEntities();

        // expect first entity to be court order
        self::assertEquals($courtOrder, $entities[0]);

        // expect second entity to be deputy, associated with court order
        self::assertEquals($deputy, $entities[1]);
        self::assertContains(['courtOrder' => $courtOrder, 'isActive' => true], $deputy->getCourtOrdersWithStatus());
    }

    public function testCreateEntitiesFromCandidatesInsertOrderDeputyFail(): void
    {
        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderDeputy, '1');
        $candidate->deputyId = 2;
        $candidate->deputyStatusOnOrder = true;

        $courtOrder = new CourtOrder();
        $this->setupMockCourtOrderRepository($courtOrder);

        $this->mockDeputyRepository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        // test
        $result = $this->sut->createEntitiesFromCandidates([$candidate]);
        $entities = $result->getEntities();
        $errors = $result->getErrors();

        // expect first (and only) entity to be court order
        self::assertEquals($courtOrder, $entities[0]);
        self::assertCount(1, $entities);

        // expect error as deputy does not exist
        self::assertCount(1, $errors);
        self::assertMatchesRegularExpression('/.*referred to non-existent deputy with ID 2.*/', $errors[0]);
    }

    public function testCreateEntitiesFromCandidatesUpdateOrderDeputyStatusFail(): void
    {
        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateDeputyStatus, '1');
        $candidate->deputyUid = '2';
        $candidate->deputyStatusOnOrder = false;

        $mockCourtOrder = $this->createMock(CourtOrder::class);

        $mockCourtOrder->expects($this->once())
            ->method('getDeputyRelationships')
            ->willReturn(new ArrayCollection([]));

        $this->setupMockCourtOrderRepository($mockCourtOrder);

        // test
        $result = $this->sut->createEntitiesFromCandidates([$candidate]);
        $entities = $result->getEntities();
        $errors = $result->getErrors();

        // only expect court order to be saved - no relationship found to update
        self::assertEquals($mockCourtOrder, $entities[0]);
        self::assertCount(1, $entities);

        // we should get a log message about relationship being absent
        self::assertCount(1, $errors);
        self::assertMatchesRegularExpression('/.*court order \(UID = 1\) to deputy \(UID = 2\) relationship does not exist.*/', $errors[0]);
    }

    public function testCreateEntitiesFromCandidatesUpdateOrderDeputyStatusSuccess(): void
    {
        // this is what we expect the status to be changed from
        $originalStatus = true;

        // this is what we're changing the status to
        $newStatus = false;

        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateDeputyStatus, '1');
        $candidate->deputyUid = '2';
        $candidate->deputyStatusOnOrder = $newStatus;

        $mockDeputy = $this->createMock(Deputy::class);
        $mockCourtOrder = $this->createMock(CourtOrder::class);

        $this->setupMockCourtOrderRepository($mockCourtOrder);

        $courtOrderDeputy = new CourtOrderDeputy();
        $courtOrderDeputy->setCourtOrder($mockCourtOrder);
        $courtOrderDeputy->setDeputy($mockDeputy);
        $courtOrderDeputy->setIsActive($originalStatus);

        $mockDeputy->expects($this->once())
            ->method('getDeputyUid')
            ->willReturn('2');

        $mockCourtOrder->expects($this->once())
            ->method('getDeputyRelationships')
            ->willReturn(new ArrayCollection([$courtOrderDeputy]));

        // test
        $result = $this->sut->createEntitiesFromCandidates([$candidate]);
        $entities = $result->getEntities();

        // always expect court order to be saved
        /** @var CourtOrder $updatedCourtOrder */
        $updatedCourtOrder = $entities[0];

        self::assertEquals($mockCourtOrder, $updatedCourtOrder);

        // additionally expect the order <-> deputy relationship status to be updated from true to false
        /** @var CourtOrderDeputy $updatedCourtOrderDeputy */
        $updatedCourtOrderDeputy = $entities[1];

        self::assertEquals($newStatus, $updatedCourtOrderDeputy->isActive());
    }

    public function testCreateEntitiesFromCandidatesInsertOrderReportFail(): void
    {
        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderReport, '1');
        $candidate->reportId = 2;

        $courtOrder = new CourtOrder();
        $this->setupMockCourtOrderRepository($courtOrder);

        $this->mockReportRepository->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn(null);

        $result = $this->sut->createEntitiesFromCandidates([$candidate]);
        $entities = $result->getEntities();

        self::assertCount(1, $entities);
        self::assertEquals($courtOrder, $entities[0]);
        self::assertEmpty($courtOrder->getReports());

        self::assertMatchesRegularExpression(
            '/.*referred to non-existent report with ID 2.*/',
            $result->getErrors()[0]
        );
    }

    public function testCreateEntitiesFromCandidatesInsertOrderReportSuccess(): void
    {
        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderReport, '1');
        $candidate->reportId = 2;

        $courtOrder = new CourtOrder();
        $this->setupMockCourtOrderRepository($courtOrder);

        $report = $this->createMock(Report::class);
        $this->mockReportRepository->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn($report);

        $result = $this->sut->createEntitiesFromCandidates([$candidate]);
        $entities = $result->getEntities();

        self::assertCount(1, $entities);
        self::assertEquals($courtOrder, $entities[0]);
        self::assertContains($report, $courtOrder->getReports());
    }

    public function testCreateEntitiesFromCandidatesInsertOrderNdrFail(): void
    {
        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderNdr, '1');
        $candidate->ndrId = 2;

        $courtOrder = new CourtOrder();
        $this->setupMockCourtOrderRepository($courtOrder);

        $this->mockNdrRepository->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn(null);

        $result = $this->sut->createEntitiesFromCandidates([$candidate]);
        $entities = $result->getEntities();

        self::assertCount(1, $entities);
        self::assertEquals($courtOrder, $entities[0]);
        self::assertNull($courtOrder->getNdr());

        self::assertMatchesRegularExpression(
            '/.*referred to non-existent NDR with ID 2.*/',
            $result->getErrors()[0]
        );
    }

    public function testCreateEntitiesFromCandidatesInsertOrderNdrSuccess(): void
    {
        $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderNdr, '1');
        $candidate->ndrId = 2;

        $courtOrder = new CourtOrder();
        $this->setupMockCourtOrderRepository($courtOrder);

        $ndr = $this->createMock(Ndr::class);
        $this->mockNdrRepository->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn($ndr);

        $result = $this->sut->createEntitiesFromCandidates([$candidate]);
        $entities = $result->getEntities();

        self::assertCount(1, $entities);
        self::assertEquals($courtOrder, $entities[0]);
        self::assertEquals($ndr, $courtOrder->getNdr());
    }
}
