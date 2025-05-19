<?php

declare(strict_types=1);

namespace App\Tests\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use App\Factory\StagingSelectedCandidateFactory;
use App\v2\Registration\DeputyshipProcessing\CourtOrderReportCandidatesFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CourtOrderReportCandidatesFactoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private StagingSelectedCandidateFactory $candidateFactory;
    private CourtOrderReportCandidatesFactory $sut;
    private Connection $connection;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->candidateFactory = $this->createMock(StagingSelectedCandidateFactory::class);
        $this->connection = $this->createMock(Connection::class);

        $this->entityManager->method('getConnection')->willReturn($this->connection);

        $this->sut = new CourtOrderReportCandidatesFactory(
            $this->entityManager,
            $this->candidateFactory
        );
    }

    public function testCreateCompatibleReportCandidates(): void
    {
        $rows = new \ArrayIterator([
            ['court_order_uid' => '123', 'report_id' => '456'],
            ['court_order_uid' => '789', 'report_id' => '012'],
        ]);

        $expectedCandidates = [
            $this->createMock(StagingSelectedCandidate::class),
            $this->createMock(StagingSelectedCandidate::class),
        ];

        $result = $this->createMock(Result::class);
        $result->method('iterateAssociative')->willReturn($rows);
        $this->connection->method('executeQuery')->willReturn($result);

        $this->candidateFactory->expects($this->exactly(2))
            ->method('createInsertOrderReportCandidate')
            ->willReturnOnConsecutiveCalls($expectedCandidates[0], $expectedCandidates[1]);

        $candidates = $this->sut->createCompatibleReportCandidates();

        $this->assertEquals($expectedCandidates, iterator_to_array($candidates));
    }

    public function testCreateCompatibleNdrCandidates(): void
    {
        $rows = new \ArrayIterator([
            ['court_order_uid' => '123', 'ndr_id' => '456'],
            ['court_order_uid' => '789', 'ndr_id' => '012'],
        ]);

        $expectedCandidates = [
            $this->createMock(StagingSelectedCandidate::class),
            $this->createMock(StagingSelectedCandidate::class),
        ];

        $result = $this->createMock(Result::class);
        $result->method('iterateAssociative')->willReturn($rows);
        $this->connection->method('executeQuery')->willReturn($result);

        $this->candidateFactory->expects($this->exactly(2))
            ->method('createInsertOrderNdrCandidate')
            ->willReturnOnConsecutiveCalls($expectedCandidates[0], $expectedCandidates[1]);

        $candidates = $this->sut->createCompatibleNdrCandidates();

        $this->assertEquals($expectedCandidates, iterator_to_array($candidates));
    }
}
