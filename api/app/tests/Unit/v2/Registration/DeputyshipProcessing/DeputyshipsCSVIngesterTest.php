<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilder;
use App\v2\Registration\DeputyshipProcessing\DeputyshipPersister;
use App\v2\Registration\DeputyshipProcessing\DeputyshipPipelineState;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngester;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngestResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsIngestResultRecorder;
use App\v2\Registration\DTO\DeputyshipRowDto;
use App\v2\Registration\Enum\DeputyshipProcessingStatus;
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

    public function rowFixtures(): array
    {
        // <status after processing the row>, <expected method call>
        return [
            [DeputyshipProcessingStatus::SKIPPED, 'recordSkippedRow'],
            [DeputyshipProcessingStatus::FAILED, 'recordFailedRow'],
            [DeputyshipProcessingStatus::SUCCEEDED, 'recordProcessedRow'],
        ];
    }

    /**
     * @dataProvider rowFixtures
     */
    public function testProcessCsvWithSkippedRow(DeputyshipProcessingStatus $expectedStatus, string $expectedMethodCall): void
    {
        $dto = new DeputyshipRowDto();
        $state = new DeputyshipPipelineState($dto, $expectedStatus);

        $candidates = [$state];

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('reset');

        $this->mockDeputyshipsCSVLoader->expects($this->once())
            ->method('load')
            ->with('/tmp/deputyships.csv')
            ->willReturn(true);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordCsvLoadResult')
            ->with('/tmp/deputyships.csv', true);

        $this->mockDeputyshipsCandidatesSelector->expects($this->once())
            ->method('select')
            ->willReturn($candidates);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('recordDeputyshipCandidates')
            ->with($candidates);

        $this->mockDeputyshipBuilder->expects($this->once())
            ->method('build')
            ->with($state)
            ->willReturn($state);

        $this->mockDeputyshipPersister->expects($this->once())
            ->method('persist')
            ->with($state)
            ->willReturn($state);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method($expectedMethodCall)
            ->with($state);

        $this->mockDeputyshipsIngestResultRecorder->expects($this->once())
            ->method('result')
            ->willReturn(new DeputyshipsCSVIngestResult(true, ''));

        $result = $this->sut->processCsv('/tmp/deputyships.csv');

        $this->assertTrue($result->success);
    }
}
