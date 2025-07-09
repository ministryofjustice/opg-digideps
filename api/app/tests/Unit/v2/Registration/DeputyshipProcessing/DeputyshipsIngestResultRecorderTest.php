<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilderResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesSelectorResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoaderResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsIngestResultRecorder;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeputyshipsIngestResultRecorderTest extends TestCase
{
    private LoggerInterface $mockLogger;
    private DeputyshipsIngestResultRecorder $sut;

    public function setUp(): void
    {
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->sut = new DeputyshipsIngestResultRecorder($this->mockLogger);
    }

    public function testRecordCsvLoadResultFailure(): void
    {
        $this->sut->recordCsvLoadResult(new DeputyshipsCSVLoaderResult('/tmp/test.csv', false));

        $result = $this->sut->result();

        self::assertFalse($result->success);
        self::assertStringContainsString('failed to load deputyships CSV from /tmp/test.csv', $result->message);
    }

    public function testRecordDeputyshipCandidatesResultExceptionFail(): void
    {
        $exception = new Exception('Database connection failed');
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(new \ArrayIterator([]), 0, $exception);
        $this->sut->recordDeputyshipCandidatesResult($candidatesSelectorResult);

        $result = $this->sut->result();

        self::assertFalse($result->success);
        self::assertStringContainsString($exception->getMessage(), $result->message);
    }

    public function testRecordDeputyshipCandidatesResultSuccess(): void
    {
        $this->sut->recordCsvLoadResult(new DeputyshipsCSVLoaderResult('/tmp/deputyships.csv', true, 10));

        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(new \ArrayIterator([]), 20, null);
        $this->sut->recordDeputyshipCandidatesResult($candidatesSelectorResult);

        $expectedMessage1 = 'loaded 10 deputyships from CSV file /tmp/deputyships.csv; found 20 candidate database '.
            'updates; number of candidates applied = 0; number of candidates failed = 0; successfully ingested '.
            'deputyships CSV';

        $result = $this->sut->result();

        self::assertTrue($result->success);
        self::assertStringContainsString($expectedMessage1, $result->message);
    }

    public function testRecordBuilderResult(): void
    {
        $builderResult1 = $this->createMock(DeputyshipBuilderResult::class);
        $builderResult1->expects($this->once())->method('getNumCandidatesApplied')->willReturn(7);
        $builderResult1->expects($this->once())->method('getNumCandidatesFailed')->willReturn(1);
        $this->sut->recordBuilderResult($builderResult1);

        $result = $this->sut->result();
        self::assertStringContainsString('number of candidates applied = 7', $result->message);
        self::assertStringContainsString('number of candidates failed = 1', $result->message);

        $builderResult2 = $this->createMock(DeputyshipBuilderResult::class);
        $builderResult2->expects($this->once())->method('getNumCandidatesApplied')->willReturn(3);
        $builderResult2->expects($this->once())->method('getNumCandidatesFailed')->willReturn(4);
        $this->sut->recordBuilderResult($builderResult2);

        $result = $this->sut->result();
        self::assertStringContainsString('number of candidates applied = 10', $result->message);
        self::assertStringContainsString('number of candidates failed = 5', $result->message);
    }

    public function testStartAndEndDateAndTimings(): void
    {
        $start = new \DateTimeImmutable('2025-05-21 23:59:00');
        $end = new \DateTimeImmutable('2025-05-22 01:05:10');

        $this->sut->recordStart($start);
        $this->sut->recordEnd($end);

        $expected = '1h 6m 10s';

        $result = $this->sut->result();

        self::assertStringContainsString($expected, $result->message);
    }

    public function testDryRun(): void
    {
        $this->sut->setDryRun(true);

        // logger should only log at warning level
        $this->mockLogger->expects($this->once())->method('warning')->willReturnCallback(
            function (string $message) {
                self::assertStringContainsString('failed to load deputyships CSV from', $message);
            }
        );

        // record a failed ingest step
        $this->sut->recordCsvLoadResult(new DeputyshipsCSVLoaderResult('/tmp/test.csv', false));
    }
}
