<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesSelectorResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsIngestResultRecorder;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\TestCase;

class DeputyshipsIngestResultRecorderTest extends TestCase
{
    private DeputyshipsIngestResultRecorder $sut;

    public function setUp(): void
    {
        $this->sut = new DeputyshipsIngestResultRecorder();
    }

    public function testRecordCsvLoadResultFailure(): void
    {
        $this->sut->recordCsvLoadResult('/tmp/test.csv', false);

        $result = $this->sut->result();

        $this->assertFalse($result->success);
        $this->assertEquals('failed to load CSV from /tmp/test.csv', $result->message);
    }

    public function testRecordDeputyshipCandidatesResultExceptionFail(): void
    {
        $exception = new Exception('Database connection failed');
        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult([], $exception);
        $this->sut->recordDeputyshipCandidatesResult($candidatesSelectorResult);

        $result = $this->sut->result();

        $this->assertFalse($result->success);
        $this->assertEquals($exception->getMessage(), $result->message);
    }

    public function testRecordDeputyshipCandidatesResultSuccess(): void
    {
        $this->sut->recordCsvLoadResult('/tmp/deputyships.csv', true);

        $candidatesSelectorResult = new DeputyshipCandidatesSelectorResult([], null);
        $this->sut->recordDeputyshipCandidatesResult($candidatesSelectorResult);

        $result = $this->sut->result();

        $this->assertTrue($result->success);
        $this->assertEquals('successfully ingested deputyships CSV', $result->message);
    }
}
