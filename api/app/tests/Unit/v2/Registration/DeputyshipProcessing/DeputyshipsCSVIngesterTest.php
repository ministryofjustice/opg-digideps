<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use App\Factory\DataFactoryInterface;
use App\Factory\DataFactoryResult;
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
use ArrayIterator;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeputyshipsCSVIngesterTest extends TestCase
{
    private DeputyshipsCSVLoader|MockObject $mockDeputyshipsCSVLoader;
    private DeputyshipsCandidatesSelector|MockObject $mockDeputyshipsCandidatesSelector;
    private DeputyshipBuilder|MockObject $mockDeputyshipBuilder;
    private DeputyshipsIngestResultRecorder|MockObject $mockDeputyshipsIngestResultRecorder;
    private DataFactoryInterface|MockObject $mockDataFactory;
    private DeputyshipsCSVIngester $sut;

    public function setUp(): void
    {
        $this->mockDeputyshipsCSVLoader = self::createMock(DeputyshipsCSVLoader::class);
        $this->mockDeputyshipsCandidatesSelector = self::createMock(DeputyshipsCandidatesSelector::class);
        $this->mockDeputyshipBuilder = self::createMock(DeputyshipBuilder::class);
        $this->mockDataFactory = self::createMock(DataFactoryInterface::class);
        $this->mockDeputyshipsIngestResultRecorder = self::createMock(DeputyshipsIngestResultRecorder::class);

        $this->sut = new DeputyshipsCSVIngester(
            $this->mockDeputyshipsCSVLoader,
            $this->mockDeputyshipsCandidatesSelector,
            $this->mockDeputyshipBuilder,
            $this->mockDataFactory,
            $this->mockDeputyshipsIngestResultRecorder
        );
    }

    public function testCsvLoadFailed(): void
    {
        $mockCsvLoaderResult = self::createMock(DeputyshipsCSVLoaderResult::class);
        $mockCsvLoaderResult->loadedOk = false;

        $this->mockDeputyshipsCSVLoader->expects(self::once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn($mockCsvLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordCsvLoadResult')
            ->with($mockCsvLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, 'failed to load CSV'));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        self::assertFalse($result->success);
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
            new ArrayIterator([]),
            0,
            new Exception('unexpected database exception')
        );

        $this->mockDeputyshipsCandidatesSelector->expects(self::once())
            ->method('select')
            ->willReturn($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordDeputyshipCandidatesResult')
            ->with($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, 'unexpected database exception'));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        self::assertFalse($result->success);
    }

    public function testDataFactoryFails(): void
    {
        $mockCSVLoaderResult = $this->createMock(DeputyshipsCSVLoaderResult::class);
        $mockCSVLoaderResult->loadedOk = true;

        $candidates = [$this->createMock(StagingSelectedCandidate::class)];
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(new ArrayIterator($candidates), 1);

        $builderResult = new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::CandidatesApplied);

        $dataFactoryResult = new DataFactoryResult(false);

        $this->mockDeputyshipsCSVLoader->expects(self::once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn($mockCSVLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordCsvLoadResult')
            ->with($mockCSVLoaderResult);

        $this->mockDeputyshipsCandidatesSelector->expects(self::once())
            ->method('select')
            ->willReturn($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordDeputyshipCandidatesResult')
            ->with($candidatesSelectorResult);

        $this->mockDeputyshipBuilder->expects(self::once())
            ->method('build')
            ->with(new ArrayIterator($candidates))
            ->willReturn(new ArrayIterator([$builderResult]));

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordBuilderResult')
            ->with($builderResult);

        $this->mockDataFactory->expects(self::once())
            ->method('run')
            ->willReturn($dataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordDataFactoryResult')
            ->with($dataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(false, ''));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        self::assertFalse($result->success);
    }

    public function testProcessCsvRows(): void
    {
        $mockCSVLoaderResult = $this->createMock(DeputyshipsCSVLoaderResult::class);
        $mockCSVLoaderResult->loadedOk = true;

        $candidates = [$this->createMock(StagingSelectedCandidate::class)];
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(new ArrayIterator($candidates), 1);

        $builderResult = new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::CandidatesApplied);

        $dataFactoryResult = new DataFactoryResult(false);

        $this->mockDeputyshipsCSVLoader->expects(self::once())
            ->method('load')
            ->with('/tmp/deputyshipsReport.csv')
            ->willReturn($mockCSVLoaderResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordCsvLoadResult')
            ->with($mockCSVLoaderResult);

        $this->mockDeputyshipsCandidatesSelector->expects(self::once())
            ->method('select')
            ->willReturn($candidatesSelectorResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordDeputyshipCandidatesResult')
            ->with($candidatesSelectorResult);

        $this->mockDeputyshipBuilder->expects(self::once())
            ->method('build')
            ->with(new ArrayIterator($candidates))
            ->willReturn(new ArrayIterator([$builderResult]));

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordBuilderResult')
            ->with($builderResult);

        $this->mockDataFactory->expects(self::once())
            ->method('run')
            ->willReturn($dataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('recordDataFactoryResult')
            ->with($dataFactoryResult);

        $this->mockDeputyshipsIngestResultRecorder->expects(self::once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(true, ''));

        $result = $this->sut->processCsv('/tmp/deputyshipsReport.csv');

        self::assertTrue($result->success);
    }
}
