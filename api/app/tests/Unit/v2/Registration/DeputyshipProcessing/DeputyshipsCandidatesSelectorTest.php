<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\Repository\StagingDeputyshipRepository;
use App\v2\Registration\DeputyshipProcessing\CourtOrderAndDeputyCandidatesFactory;
use App\v2\Registration\DeputyshipProcessing\CourtOrderReportCandidatesFactory;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeputyshipsCandidatesSelectorTest extends TestCase
{
    private EntityManagerInterface&MockObject $mockEntityManager;
    private StagingDeputyshipRepository&MockObject $mockStagingDeputyshipRepository;
    private CourtOrderAndDeputyCandidatesFactory&MockObject $mockCourtOrderAndDeputyCandidatesFactory;
    private CourtOrderReportCandidatesFactory&MockObject $mockCourtOrderReportCandidatesFactory;
    private DeputyshipsCandidatesSelector $sut;

    public function setUp(): void
    {
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->mockStagingDeputyshipRepository = $this->createMock(StagingDeputyshipRepository::class);
        $this->mockCourtOrderAndDeputyCandidatesFactory = $this->createMock(CourtOrderAndDeputyCandidatesFactory::class);
        $this->mockCourtOrderReportCandidatesFactory = $this->createMock(CourtOrderReportCandidatesFactory::class);

        $this->sut = new DeputyshipsCandidatesSelector(
            $this->mockEntityManager,
            $this->mockStagingDeputyshipRepository,
            $this->mockCourtOrderAndDeputyCandidatesFactory,
            $this->mockCourtOrderReportCandidatesFactory,
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
        $this->assertEquals([], $result->candidates);
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
            ->expects($this->exactly(2))
            ->method('flush');

        $this->mockEntityManager
            ->expects($this->once())
            ->method('commit');

        $mockStagingDeputyship1 = $this->createMock(StagingDeputyship::class);
        $mockStagingDeputyship2 = $this->createMock(StagingDeputyship::class);

        $this->mockStagingDeputyshipRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$mockStagingDeputyship1, $mockStagingDeputyship2]);

        $this->mockCourtOrderAndDeputyCandidatesFactory
            ->expects($this->once())
            ->method('cacheLookupTables');

        $mockCandidate1 = new StagingSelectedCandidate();
        $mockCandidate2 = new StagingSelectedCandidate();
        $mockCandidate3 = new StagingSelectedCandidate();
        $mockCandidate4 = new StagingSelectedCandidate();
        $mockCandidate5 = new StagingSelectedCandidate();
        $mockCandidate6 = new StagingSelectedCandidate();

        $this->mockCourtOrderAndDeputyCandidatesFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls([$mockCandidate1], [$mockCandidate2, $mockCandidate3]);

        $this->mockCourtOrderReportCandidatesFactory
            ->expects($this->once())
            ->method('createCompatibleReportCandidates')
            ->willReturn([$mockCandidate4]);

        $this->mockCourtOrderReportCandidatesFactory
            ->expects($this->once())
            ->method('createNewReportCandidates')
            ->willReturn([$mockCandidate5]);

        $this->mockCourtOrderReportCandidatesFactory
            ->expects($this->once())
            ->method('createCompatibleNdrCandidates')
            ->willReturn([$mockCandidate6]);

        $mockCandidates = [
            $mockCandidate1,
            $mockCandidate2,
            $mockCandidate3,
            $mockCandidate4,
            $mockCandidate5,
            $mockCandidate6,
        ];

        $this->mockEntityManager
            ->expects($this->exactly(6))
            ->method('persist')
            ->willReturnCallback(function ($entity) use ($mockCandidates) {
                self::assertContains($entity, $mockCandidates);
            });

        $result = $this->sut->select();

        $this->assertNull($result->exception);
        $this->assertTrue($result->success());
        $this->assertEquals($mockCandidates, $result->candidates);
    }
}
