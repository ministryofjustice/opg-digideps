<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesSelectorResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsIngestResultRecorder;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeputyshipsIngestResultRecorderTest extends TestCase
{
    private DeputyshipsIngestResultRecorder $sut;

    public function setUp(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $this->sut = new DeputyshipsIngestResultRecorder($mockLogger);
    }

    public function testRecordCsvLoadResultFailure(): void
    {
        $this->sut->recordCsvLoadResult('/tmp/test.csv', false);

        $result = $this->sut->result();

        $this->assertFalse($result->success);
        $this->assertEquals('failed to load deputyships CSV from /tmp/test.csv', $result->message);
    }

    public function testRecordDeputyshipCandidatesResultExceptionFail(): void
    {
        $exception = new Exception('Database connection failed');
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(new \ArrayIterator([]), 0, $exception);
        $this->sut->recordDeputyshipCandidatesResult($candidatesSelectorResult);

        $result = $this->sut->result();

        $this->assertFalse($result->success);
        $this->assertEquals($exception->getMessage(), $result->message);
    }

    public function testRecordDeputyshipCandidatesResultSuccess(): void
    {
        $this->sut->recordCsvLoadResult('/tmp/deputyships.csv', true);

        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult(new \ArrayIterator([]), 20, null);
        $this->sut->recordDeputyshipCandidatesResult($candidatesSelectorResult);

        $expectedMessage = 'loaded deputyships CSV from /tmp/deputyships.csv; '.
            'found 20 candidate database updates; successfully ingested deputyships CSV';

        $result = $this->sut->result();

        $this->assertTrue($result->success);
        $this->assertEquals($expectedMessage, $result->message);
    }
}
