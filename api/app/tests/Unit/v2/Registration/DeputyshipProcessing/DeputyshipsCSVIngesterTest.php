<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilder;
use App\v2\Registration\DeputyshipProcessing\DeputyshipEntityMatcher;
use App\v2\Registration\DeputyshipProcessing\DeputyshipPersister;
use App\v2\Registration\DeputyshipProcessing\DeputyshipPipelineState;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngester;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngestResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsIngestResultRecorder;
use App\v2\Registration\DTO\DeputyshipRowDto;
use App\v2\Registration\Enum\DeputyshipProcessingStatus;
use League\Csv\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeputyshipsCSVIngesterTest extends TestCase
{
    private DeputyshipEntityMatcher|MockObject $mockDeputyshipEntityMatcher;
    private DeputyshipBuilder|MockObject $mockDeputyshipBuilder;
    private DeputyshipsIngestResultRecorder|MockObject $mockDeputyshipsIngestResultRecorder;
    private DeputyshipPersister|MockObject $mockDeputyshipPersister;
    private DeputyshipsCSVIngester $sut;

    public function setUp(): void
    {
        $this->mockDeputyshipEntityMatcher = $this->createMock(DeputyshipEntityMatcher::class);
        $this->mockDeputyshipBuilder = $this->createMock(DeputyshipBuilder::class);
        $this->mockDeputyshipsIngestResultRecorder = $this->createMock(DeputyshipsIngestResultRecorder::class);
        $this->mockDeputyshipPersister = $this->createMock(DeputyshipPersister::class);

        $this->sut = new DeputyshipsCSVIngester(
            $this->mockDeputyshipEntityMatcher,
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

        $reader = $this->createMock(Reader::class);
        $reader->expects($this->once())
            ->method('getRecordsAsObject')
            ->willReturn(new \ArrayIterator([$dto]));

        $this->mockDeputyshipEntityMatcher->expects($this->once())
            ->method('match')
            ->with(self::isInstanceOf(DeputyshipPipelineState::class))
            ->willReturn($state);

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

        $result = $this->sut->processCsv($reader);

        $this->assertTrue($result->success);
    }
}
