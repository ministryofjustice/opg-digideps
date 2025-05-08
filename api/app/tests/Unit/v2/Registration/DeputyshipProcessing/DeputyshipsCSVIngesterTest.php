<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilder;
use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilderResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesSelectorResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipPersister;
use App\v2\Registration\DeputyshipProcessing\DeputyshipPersisterResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngester;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngestResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsIngestResultRecorder;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeputyshipsCSVIngesterTest extends TestCase
{
    private DeputyshipsCSVLoader|MockObject $mockDeputyshipsCSVLoader;
    private DeputyshipsCandidatesSelector|MockObject $mockDeputyshipsCandidatesSelector;
    private DeputyshipBuilder|MockObject $mockDeputyshipBuilder;
    private DeputyshipPersister|MockObject $mockDeputyshipPersister;
    private DeputyshipsIngestResultRecorder|MockObject $mockDeputyshipsIngestResultRecorder;
    private DeputyshipsCSVIngester $sut;

    public function setUp(): void
    {
        $this->mockDeputyshipsCSVLoader = $this->createMock(DeputyshipsCSVLoader::class);
        $this->mockDeputyshipsCandidatesSelector = $this->createMock(DeputyshipsCandidatesSelector::class);
        $this->mockDeputyshipBuilder = $this->createMock(DeputyshipBuilder::class);
        $this->mockDeputyshipPersister = $this->createMock(DeputyshipPersister::class);
        $this->mockDeputyshipsIngestResultRecorder = $this->createMock(DeputyshipsIngestResultRecorder::class);

        $this->sut = new DeputyshipsCSVIngester(
            $this->mockDeputyshipsCSVLoader,
            $this->mockDeputyshipsCandidatesSelector,
            $this->mockDeputyshipBuilder,
            $this->mockDeputyshipPersister,
            $this->mockDeputyshipsIngestResultRecorder
        );
    }

    public function testCsvLoadFailed(): void
    {
        $this->mockDeputyshipsCSVLoader->expects($this->once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn(false);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordCsvLoadResult')
            ->with('/tmp/deputyshipsReport.csv', false);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, 'failed to load CSV'));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        $this->assertFalse($result->success);
    }

    public function testCandidateSelectionFailed(): void
    {
        // CSV load goes OK
        $this->mockDeputyshipsCSVLoader->method('load')->willReturn(true);
        $this->mockDeputyshipsIngestResultRecorder->method('recordCsvLoadResult');

        // candidate selection fails
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(
            [],
            0,
            new Exception('unexpected database exception')
        );

        $this->mockDeputyshipsCandidatesSelector->expects($this->once())
            ->method('select')
            ->willReturn($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordDeputyshipCandidatesResult')
            ->with($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, 'unexpected database exception'));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        $this->assertFalse($result->success);
    }

    public function testProcessCsvRows(): void
    {
        $builderResult = new DeputyshipBuilderResult([]);
        $persisterResult = new DeputyshipPersisterResult();

        $candidates = [new StagingSelectedCandidate()];
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult($candidates, 1);

        $this->mockDeputyshipsCSVLoader->expects($this->once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn(true);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordCsvLoadResult')
            ->with('/tmp/deputyshipsReport.csv', true);

        $this->mockDeputyshipsCandidatesSelector->expects($this->once())
            ->method('select')
            ->willReturn($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordDeputyshipCandidatesResult')
            ->with($candidatesSelectorResult);

        $this->mockDeputyshipBuilder->expects($this->once())
            ->method('build')
            ->with($candidates)
            ->willReturn([$builderResult]);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordBuilderResult')
            ->with($builderResult);

        $this->mockDeputyshipPersister->expects($this->once())
            ->method('persist')
            ->with($builderResult)
            ->willReturn([$persisterResult]);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordPersisterResult')
            ->with($persisterResult);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(true, ''));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        $this->assertTrue($result->success);
    }
}
