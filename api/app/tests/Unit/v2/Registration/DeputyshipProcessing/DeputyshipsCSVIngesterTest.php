<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilder;
use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilderResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesSelectorResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngester;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngestResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoaderResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsIngestResultRecorder;
use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeputyshipsCSVIngesterTest extends TestCase
{
    private DeputyshipsCSVLoader|MockObject $mockDeputyshipsCSVLoader;
    private DeputyshipsCandidatesSelector|MockObject $mockDeputyshipsCandidatesSelector;
    private DeputyshipBuilder|MockObject $mockDeputyshipBuilder;
    private DeputyshipsIngestResultRecorder|MockObject $mockDeputyshipsIngestResultRecorder;
    private DeputyshipsCSVIngester $sut;

    public function setUp(): void
    {
        $this->mockDeputyshipsCSVLoader = $this->createMock(DeputyshipsCSVLoader::class);
        $this->mockDeputyshipsCandidatesSelector = $this->createMock(DeputyshipsCandidatesSelector::class);
        $this->mockDeputyshipBuilder = $this->createMock(DeputyshipBuilder::class);
        $this->mockDeputyshipsIngestResultRecorder = $this->createMock(DeputyshipsIngestResultRecorder::class);

        $this->sut = new DeputyshipsCSVIngester(
            $this->mockDeputyshipsCSVLoader,
            $this->mockDeputyshipsCandidatesSelector,
            $this->mockDeputyshipBuilder,
            $this->mockDeputyshipsIngestResultRecorder
        );
    }

    public function testCsvLoadFailed(): void
    {
        $mockCsvLoaderResult = $this->createMock(DeputyshipsCSVLoaderResult::class);
        $mockCsvLoaderResult->loadedOk = false;

        $this->mockDeputyshipsCSVLoader->expects($this->once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn($mockCsvLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordCsvLoadResult')
            ->with($mockCsvLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, 'failed to load CSV'));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        $this->assertFalse($result->success);
    }

    public function testCandidateSelectionFailed(): void
    {
        // CSV load goes OK
        $mockCSVLoaderResult = $this->createMock(DeputyshipsCSVLoaderResult::class);
        $mockCSVLoaderResult->loadedOk = true;
        $this->mockDeputyshipsCSVLoader->method('load')->willReturn($mockCSVLoaderResult);
        $this->mockDeputyshipsIngestResultRecorder->method('recordCsvLoadResult');

        // candidate selection fails
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(
            new \ArrayIterator([]),
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
        $builderResult = new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::CandidatesApplied);

        $candidates = [$this->createMock(StagingSelectedCandidate::class)];
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(new \ArrayIterator($candidates), 1);

        $mockCSVLoaderResult = $this->createMock(DeputyshipsCSVLoaderResult::class);
        $mockCSVLoaderResult->loadedOk = true;

        $this->mockDeputyshipsCSVLoader->expects($this->once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn($mockCSVLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordCsvLoadResult')
            ->with($mockCSVLoaderResult);

        $this->mockDeputyshipsCandidatesSelector->expects($this->once())
            ->method('select')
            ->willReturn($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordDeputyshipCandidatesResult')
            ->with($candidatesSelectorResult);

        $this->mockDeputyshipBuilder->expects($this->once())
            ->method('build')
            ->with(new \ArrayIterator($candidates))
            ->willReturn(new \ArrayIterator([$builderResult]));

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordBuilderResult')
            ->with($builderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(true, ''));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        $this->assertTrue($result->success);
    }
}
