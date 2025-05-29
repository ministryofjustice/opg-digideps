<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\Repository\StagingDeputyshipRepository;
use App\Repository\StagingSelectedCandidateRepository;
use App\v2\Registration\DeputyshipProcessing\CourtOrderAndDeputyCandidatesFactory;
use App\v2\Registration\DeputyshipProcessing\CourtOrderReportCandidatesFactory;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeputyshipsCandidatesSelectorTest extends TestCase
{
    private EntityManagerInterface&MockObject $mockEntityManager;
    private StagingDeputyshipRepository&MockObject $mockStagingDeputyshipRepository;
    private CourtOrderAndDeputyCandidatesFactory&MockObject $mockCourtOrderAndDeputyCandidatesFactory;
    private CourtOrderReportCandidatesFactory&MockObject $mockCourtOrderReportCandidatesFactory;
    private StagingSelectedCandidateRepository&MockObject $mockStagingSelectedCandidateRepository;
    private LoggerInterface&MockObject $mockLogger;
    private DeputyshipsCandidatesSelector $sut;

    public function setUp(): void
    {
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->mockStagingDeputyshipRepository = $this->createMock(StagingDeputyshipRepository::class);
        $this->mockCourtOrderAndDeputyCandidatesFactory = $this->createMock(CourtOrderAndDeputyCandidatesFactory::class);
        $this->mockCourtOrderReportCandidatesFactory = $this->createMock(CourtOrderReportCandidatesFactory::class);
        $this->mockStagingSelectedCandidateRepository = $this->createMock(StagingSelectedCandidateRepository::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->sut = new DeputyshipsCandidatesSelector(
            $this->mockEntityManager,
            $this->mockStagingDeputyshipRepository,
            $this->mockCourtOrderAndDeputyCandidatesFactory,
            $this->mockCourtOrderReportCandidatesFactory,
            $this->mockStagingSelectedCandidateRepository,
            $this->mockLogger,
        );
    }

    public function testSelectDbException(): void
    {
        // so that the test will run: we check all of this in the successful test
        $mockQuery = $this->createMock(AbstractQuery::class);
        $this->mockEntityManager->method('createQuery')->willReturn($mockQuery);
        $this->mockStagingDeputyshipRepository->method('findAll')->willReturn([]);

        // thrown an exception when calling a method on the report candidates factory
        $expectedException = new Exception('unexpected db exception');
        $this->mockCourtOrderReportCandidatesFactory->expects($this->once())
            ->method('createCompatibleReportCandidates')
            ->willThrowException($expectedException);

        $result = $this->sut->select();

        $this->assertFalse($result->success());
        $this->assertEquals([], iterator_to_array($result->candidates));
        $this->assertEquals($expectedException, $result->exception);
    }

    public function testSelect(): void
    {
        $this->mockEntityManager
            ->expects($this->once())
            ->method('beginTransaction');

        $mockQuery = $this->createMock(AbstractQuery::class);
        $mockQuery->expects($this->once())
            ->method('execute');

        $this->mockEntityManager
            ->expects($this->once())
            ->method('createQuery')
            ->with('DELETE FROM App\Entity\StagingSelectedCandidate sc')
            ->willReturn($mockQuery);

        $this->mockEntityManager
            ->expects($this->exactly(5))
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
        $mockCandidate5 = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateOrderStatus, '1');

        $this->mockCourtOrderAndDeputyCandidatesFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls([$mockCandidate1], [$mockCandidate2, $mockCandidate3]);

        $this->mockCourtOrderReportCandidatesFactory
            ->expects($this->once())
            ->method('createCompatibleReportCandidates')
            ->willReturn(new \ArrayIterator([$mockCandidate4]));

        $this->mockCourtOrderReportCandidatesFactory
            ->expects($this->once())
            ->method('createCompatibleNdrCandidates')
            ->willReturn(new \ArrayIterator([$mockCandidate5]));

        $mockCandidates = [
            $mockCandidate1,
            $mockCandidate2,
            $mockCandidate3,
            $mockCandidate4,
            $mockCandidate5,
        ];

        $this->mockEntityManager
            ->expects($this->exactly(5))
            ->method('persist')
            ->willReturnCallback(function ($entity) use ($mockCandidates) {
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
