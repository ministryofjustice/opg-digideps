<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\CourtOrder;
use App\Entity\StagingSelectedCandidate;
use App\Repository\CourtOrderRepository;
use App\Repository\DeputyRepository;
use App\Repository\ReportRepository;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeputyshipCandidateConverterTest extends TestCase
{
    private CourtOrderRepository&MockObject $mockCourtOrderRepository;
    private DeputyRepository&MockObject $mockDeputyRepository;
    private ReportRepository&MockObject $mockReportRepository;
    private LoggerInterface&MockObject $mockLogger;
    private DeputyshipCandidateConverter $sut;

    public function setUp(): void
    {
        $this->mockCourtOrderRepository = $this->createMock(CourtOrderRepository::class);
        $this->mockDeputyRepository = $this->createMock(DeputyRepository::class);
        $this->mockReportRepository = $this->createMock(ReportRepository::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->sut = new DeputyshipCandidateConverter(
            $this->mockCourtOrderRepository,
            $this->mockDeputyRepository,
            $this->mockReportRepository,
            $this->mockLogger,
        );
    }

    public function testCreateEntitiesFromCandidatesMultipleOrderUidsFail(): void
    {
        $candidate1 = new StagingSelectedCandidate();
        $candidate1->orderUid = '1';

        $candidate2 = new StagingSelectedCandidate();
        $candidate2->orderUid = '2';

        $candidates = [$candidate1, $candidate2];

        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with(self::matchesRegularExpression('/.*more than one order UID.*/'));

        $entities = $this->sut->createEntitiesFromCandidates($candidates);

        self::assertEquals([], $entities);
    }

    public function testCreateEntitiesFromCandidatesMissingValuesFail(): void
    {
        $candidate1 = new StagingSelectedCandidate();
        $candidate1->action = DeputyshipCandidateAction::InsertOrder;
        $candidate1->orderUid = '1';

        $candidates = [$candidate1];

        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with(self::matchesRegularExpression('/.*court order could not be created.*/'));

        $entities = $this->sut->createEntitiesFromCandidates($candidates);

        self::assertEquals([], $entities);
    }

    public function testCreateEntitiesFromCandidatesNoOrderInsertOrOrderFoundFail(): void
    {
        $candidate1 = new StagingSelectedCandidate();
        $candidate1->action = DeputyshipCandidateAction::UpdateOrderStatus;
        $candidate1->orderUid = '1';

        $candidates = [$candidate1];

        $this->mockCourtOrderRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['courtOrderUid' => '1'])
            ->willReturn(null);

        $this->mockLogger->expects($this->once())
            ->method('error')
            ->with(self::matchesRegularExpression('/.*non-existent court order with UID 1.*/'));

        $entities = $this->sut->createEntitiesFromCandidates($candidates);

        self::assertEquals([], $entities);
    }

    public function testCreateEntitiesFromCandidatesInsertOrderSuccess(): void
    {
        $candidate = new StagingSelectedCandidate();
        $candidate->action = DeputyshipCandidateAction::InsertOrder;
        $candidate->orderUid = '1';
        $candidate->orderType = 'pfa';
        $candidate->status = 'ACTIVE';
        $candidate->orderMadeDate = '2018-01-21';

        $entities = $this->sut->createEntitiesFromCandidates([$candidate]);

        // expect there to be one court order entity created
        /** @var CourtOrder $courtOrder */
        $courtOrder = end($entities);

        self::assertEquals('ACTIVE', $courtOrder->getStatus());
        self::assertEquals('1', $courtOrder->getCourtOrderUid());
        self::assertEquals(new \DateTime('2018-01-21'), $courtOrder->getOrderMadeDate());
        self::assertEquals('pfa', $courtOrder->getOrderType());
    }

    public function testCreateEntitiesFromCandidatesUpdateOrderStatusSuccess(): void
    {
        $candidate1 = new StagingSelectedCandidate();
        $candidate1->action = DeputyshipCandidateAction::InsertOrder;
        $candidate1->orderUid = '1';
        $candidate1->orderType = 'pfa';
        $candidate1->status = 'ACTIVE';
        $candidate1->orderMadeDate = '2018-01-21';

        $candidate2 = new StagingSelectedCandidate();
        $candidate2->action = DeputyshipCandidateAction::UpdateOrderStatus;
        $candidate2->orderUid = '1';
        $candidate2->status = 'CLOSED';

        $entities = $this->sut->createEntitiesFromCandidates([$candidate1, $candidate2]);

        // expect there to be one court order entity created, and it should be the first item in the list;
        // as there was an update to its status, it should be set to the value from the update candidate
        /** @var CourtOrder $courtOrder */
        $courtOrder = $entities[0];

        self::assertEquals('CLOSED', $courtOrder->getStatus());
        self::assertEquals('1', $courtOrder->getCourtOrderUid());
        self::assertEquals(new \DateTime('2018-01-21'), $courtOrder->getOrderMadeDate());
        self::assertEquals('pfa', $courtOrder->getOrderType());
    }
}
