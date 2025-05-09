<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\Entity\CourtOrderDeputy;
use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\Factory\StagingSelectedCandidateFactory;
use App\Model\DeputyshipProcessingLookupCache;
use App\Repository\CourtOrderDeputyRepository;
use App\v2\Registration\DeputyshipProcessing\CourtOrderAndDeputyCandidatesFactory;
use PHPUnit\Framework\TestCase;

class CourtOrderAndDeputyCandidatesFactoryTest extends TestCase
{
    private CourtOrderDeputyRepository $courtOrderDeputyRepository;
    private DeputyshipProcessingLookupCache $deputyshipLookupCache;
    private StagingSelectedCandidateFactory $candidateFactory;
    private CourtOrderAndDeputyCandidatesFactory $sut;

    protected function setUp(): void
    {
        $this->courtOrderDeputyRepository = $this->createMock(CourtOrderDeputyRepository::class);
        $this->deputyshipLookupCache = $this->createMock(DeputyshipProcessingLookupCache::class);
        $this->candidateFactory = $this->createMock(StagingSelectedCandidateFactory::class);

        $this->sut = new CourtOrderAndDeputyCandidatesFactory(
            $this->courtOrderDeputyRepository,
            $this->deputyshipLookupCache,
            $this->candidateFactory
        );
    }

    public function testCacheLookupTables()
    {
        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('init');

        $this->sut->cacheLookupTables();
    }

    public function testCreateInsertNewCourtOrder()
    {
        $stagingDeputyship = new StagingDeputyship();
        $stagingDeputyship->orderUid = '700000001101';
        $stagingDeputyship->deputyUid = '700761111001';
        $stagingDeputyship->caseNumber = '1234567T';

        $existingClientId = 1;
        $expectedCandidate = $this->createMock(StagingSelectedCandidate::class);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getCourtOrderIdForUid')
            ->with('700000001101')
            ->willReturn(null);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getDeputyIdForUid')
            ->with('700761111001')
            ->willReturn(null);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getClientIdForCasenumber')
            ->with('1234567T')
            ->willReturn($existingClientId);

        $this->candidateFactory
            ->expects($this->once())
            ->method('createInsertOrderCandidate')
            ->with($stagingDeputyship, $existingClientId)
            ->willReturn($expectedCandidate);

        $actualCandidates = $this->sut->create($stagingDeputyship);

        $this->assertEquals([$expectedCandidate], $actualCandidates);
    }

    public function testCreateInsertNewCourtOrderAndDeputyRelationship()
    {
        $stagingDeputyship = new StagingDeputyship();
        $stagingDeputyship->orderUid = '700000001102';
        $stagingDeputyship->deputyUid = '700761111002';
        $stagingDeputyship->caseNumber = '12345678';

        $existingClientId = 1;
        $existingDeputyId = 2;
        $expectedCourtOrderCandidate = $this->createMock(StagingSelectedCandidate::class);
        $expectedCourtOrderDeputyCandidate = $this->createMock(StagingSelectedCandidate::class);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getCourtOrderIdForUid')
            ->with('700000001102')
            ->willReturn(null);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getDeputyIdForUid')
            ->with('700761111002')
            ->willReturn($existingDeputyId);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getClientIdForCasenumber')
            ->with('12345678')
            ->willReturn($existingClientId);

        $this->candidateFactory
            ->expects($this->once())
            ->method('createInsertOrderCandidate')
            ->with($stagingDeputyship, $existingClientId)
            ->willReturn($expectedCourtOrderCandidate);

        $this->candidateFactory
            ->expects($this->once())
            ->method('createInsertOrderDeputyCandidate')
            ->with($stagingDeputyship, $existingDeputyId)
            ->willReturn($expectedCourtOrderDeputyCandidate);

        $actualCandidates = $this->sut->create($stagingDeputyship);

        $this->assertEquals([$expectedCourtOrderCandidate, $expectedCourtOrderDeputyCandidate], $actualCandidates);
    }

    public function testCreateUpdateCourtOrderStatus()
    {
        $stagingDeputyship = new StagingDeputyship();
        $stagingDeputyship->orderUid = '700000001103';
        $stagingDeputyship->deputyUid = '700761111003';
        $stagingDeputyship->orderStatus = 'ACTIVE';
        $stagingDeputyship->caseNumber = '12345678';

        $existingCourtOrderId = 1;
        $currentOrderStatus = 'OPEN';
        $expectedCandidate = $this->createMock(StagingSelectedCandidate::class);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getCourtOrderIdForUid')
            ->with('700000001103')
            ->willReturn($existingCourtOrderId);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getCourtOrderStatusForUid')
            ->with('700000001103')
            ->willReturn($currentOrderStatus);

        $this->candidateFactory
            ->expects($this->once())
            ->method('createUpdateOrderStatusCandidate')
            ->with($stagingDeputyship, $existingCourtOrderId)
            ->willReturn($expectedCandidate);

        $actualCandidates = $this->sut->create($stagingDeputyship);

        $this->assertEquals([$expectedCandidate], $actualCandidates);
    }

    public function testCreateNoCourtOrderUpdateWhenStatusSame()
    {
        $stagingDeputyship = new StagingDeputyship();
        $stagingDeputyship->orderUid = '700000001104';
        $stagingDeputyship->deputyUid = '700761111004';
        $stagingDeputyship->orderStatus = 'OPEN';
        $stagingDeputyship->caseNumber = '12345678';

        $existingCourtOrderId = 1;
        $currentOrderStatus = 'OPEN';

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getCourtOrderIdForUid')
            ->with('700000001104')
            ->willReturn($existingCourtOrderId);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getCourtOrderStatusForUid')
            ->with('700000001104')
            ->willReturn($currentOrderStatus);

        $this->candidateFactory
            ->expects($this->never())
            ->method('createUpdateOrderStatusCandidate');

        $actualCandidates = $this->sut->create($stagingDeputyship);

        $this->assertEquals([], $actualCandidates);
    }

    public function testCreateInsertNewCourtOrderDeputyRelationship()
    {
        $stagingDeputyship = new StagingDeputyship();
        $stagingDeputyship->orderUid = '700000001105';
        $stagingDeputyship->deputyUid = '700761111005';
        $stagingDeputyship->deputyStatusOnOrder = 'ACTIVE';
        $stagingDeputyship->caseNumber = '987654321';
        $stagingDeputyship->orderStatus = null;

        $existingCourtOrderId = 1;
        $existingDeputyId = 2;
        $expectedCandidate = $this->createMock(StagingSelectedCandidate::class);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getCourtOrderIdForUid')
            ->with('700000001105')
            ->willReturn($existingCourtOrderId);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getDeputyIdForUid')
            ->with('700761111005')
            ->willReturn($existingDeputyId);

        $this->courtOrderDeputyRepository
            ->expects($this->once())
            ->method('getDeputyOnCourtOrder')
            ->with($existingCourtOrderId, $existingDeputyId)
            ->willReturn(null);

        $this->candidateFactory
            ->expects($this->once())
            ->method('createInsertOrderDeputyCandidate')
            ->with($stagingDeputyship, $existingDeputyId)
            ->willReturn($expectedCandidate);

        $actualCandidates = $this->sut->create($stagingDeputyship);

        $this->assertEquals([$expectedCandidate], $actualCandidates);
    }

    public function testCreateUpdateCourtOrderDeputyRelationship()
    {
        $stagingDeputyship = new StagingDeputyship();
        $stagingDeputyship->orderUid = '700000001106';
        $stagingDeputyship->deputyUid = '700761111006';
        $stagingDeputyship->deputyStatusOnOrder = 'ACTIVE';
        $stagingDeputyship->caseNumber = '88838884823';
        $stagingDeputyship->orderStatus = 'ACTIVE';

        $existingCourtOrderId = 1;
        $existingDeputyId = 2;

        $expectedCandidate = $this->createMock(StagingSelectedCandidate::class);

        $existingRelationship = new CourtOrderDeputy();
        $existingRelationship->setIsActive(false);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getCourtOrderIdForUid')
            ->with('700000001106')
            ->willReturn($existingCourtOrderId);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getDeputyIdForUid')
            ->with('700761111006')
            ->willReturn($existingDeputyId);

        $this->deputyshipLookupCache
            ->expects($this->once())
            ->method('getCourtOrderStatusForUid')
            ->with('700000001106')
            ->willReturn('ACTIVE');

        $this->courtOrderDeputyRepository
            ->expects($this->once())
            ->method('getDeputyOnCourtOrder')
            ->with($existingCourtOrderId, $existingDeputyId)
            ->willReturn($existingRelationship);

        $this->candidateFactory
            ->expects($this->once())
            ->method('createUpdateDeputyStatusCandidate')
            ->with($stagingDeputyship, $existingDeputyId, $existingCourtOrderId)
            ->willReturn($expectedCandidate);

        $actualCandidates = $this->sut->create($stagingDeputyship);

        $this->assertEquals([$expectedCandidate], $actualCandidates);
    }
}
