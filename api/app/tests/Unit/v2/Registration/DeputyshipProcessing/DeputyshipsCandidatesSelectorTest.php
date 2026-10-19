<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\v2\Registration\DeputyshipProcessing;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Entity\Staging\StagingDeputyship;
use OPG\Digideps\Backend\Entity\Staging\StagingSelectedCandidate;
use OPG\Digideps\Backend\Repository\StagingDeputyshipRepository;
use OPG\Digideps\Backend\Repository\StagingSelectedCandidateRepository;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrderAndDeputyCandidatesFactory;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class DeputyshipsCandidatesSelectorTest extends TestCase
{
    private EntityManagerInterface&MockObject $mockEntityManager;
    private StagingDeputyshipRepository&MockObject $mockStagingDeputyshipRepository;
    private CourtOrderAndDeputyCandidatesFactory&MockObject $mockCourtOrderAndDeputyCandidatesFactory;
    private StagingSelectedCandidateRepository&MockObject $mockStagingSelectedCandidateRepository;
    private LoggerInterface&MockObject $mockLogger;
    private DeputyshipsCandidatesSelector $sut;

    public function setUp(): void
    {
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->mockStagingDeputyshipRepository = $this->createMock(StagingDeputyshipRepository::class);
        $this->mockCourtOrderAndDeputyCandidatesFactory = $this->createMock(CourtOrderAndDeputyCandidatesFactory::class);
        $this->mockStagingSelectedCandidateRepository = $this->createMock(StagingSelectedCandidateRepository::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->sut = new DeputyshipsCandidatesSelector(
            $this->mockEntityManager,
            $this->mockStagingDeputyshipRepository,
            $this->mockCourtOrderAndDeputyCandidatesFactory,
            $this->mockStagingSelectedCandidateRepository,
            $this->mockLogger,
        );
    }

    public function testSelect(): void
    {
        $this->mockEntityManager
            ->expects($this->once())
            ->method('beginTransaction');

        $mockQuery = $this->createMock(Query::class);
        $mockQuery->expects($this->once())
            ->method('execute');

        $qualified = StagingSelectedCandidate::class;
        $this->mockEntityManager
            ->expects($this->once())
            ->method('createQuery')
            ->with("DELETE FROM {$qualified} sc")
            ->willReturn($mockQuery);

        $this->mockEntityManager
            ->expects($this->exactly(3))
            ->method('flush');

        $this->mockEntityManager
            ->expects($this->once())
            ->method('commit');

        $mockStagingDeputyship1 = $this->createMock(StagingDeputyship::class);
        $mockStagingDeputyship2 = $this->createMock(StagingDeputyship::class);

        $this->mockStagingDeputyshipRepository
            ->expects($this->once())
            ->method('findAllPaged')
            ->willReturn(new \ArrayIterator([$mockStagingDeputyship1, $mockStagingDeputyship2]));

        $this->mockCourtOrderAndDeputyCandidatesFactory
            ->expects($this->once())
            ->method('cacheLookupTables');

        $mockCandidate1 = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateOrderStatus, '1');
        $mockCandidate2 = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateOrderStatus, '1');
        $mockCandidate3 = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateOrderStatus, '1');
        $mockCandidate4 = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateOrderStatus, '1');

        $this->mockCourtOrderAndDeputyCandidatesFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls([$mockCandidate1], [$mockCandidate2, $mockCandidate3]);

        $mockCandidates = [
            $mockCandidate1,
            $mockCandidate2,
            $mockCandidate3,
            $mockCandidate4,
        ];

        $this->mockEntityManager
            ->expects($this->exactly(3))
            ->method('persist')
            ->willReturnCallback(function ($entity) use ($mockCandidates): void {
                self::assertContains($entity, $mockCandidates);
            });

        $this->mockStagingSelectedCandidateRepository->expects($this->once())
            ->method('getDistinctOrderedCandidates')
            ->willReturn(new \ArrayIterator($mockCandidates));

        $result = $this->sut->select();

        $this->assertNull($result->exception);
        $this->assertTrue($result->success());
        $this->assertEquals($mockCandidates, iterator_to_array($result->candidates));
    }
}
