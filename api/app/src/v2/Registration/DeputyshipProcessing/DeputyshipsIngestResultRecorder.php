<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipsIngestResultRecorder
{
    /**
     * Prepare the recorder for a new ingest (remove any existing logging).
     */
    public function reset(): void
    {
    }

    /**
     * Record the result of loading the CSV file into the staging table.
     */
    public function recordCsvLoadResult(string $fileLocation, bool $loadedOk): void
    {
    }

    /**
     * Record the candidate records found which will result in some database processing.
     *
     * @param DeputyshipPipelineState[] $candidates
     */
    public function recordDeputyshipCandidates(array $candidates)
    {
    }

    public function recordSkippedRow(DeputyshipPipelineState $state): void
    {
    }

    public function recordFailedRow(DeputyshipPipelineState $state): void
    {
    }

    public function recordProcessedRow(DeputyshipPipelineState $state): void
    {
    }

    public function result(): DeputyshipsCSVIngestResult
    {
        return new DeputyshipsCSVIngestResult(true, '');
    }
}
