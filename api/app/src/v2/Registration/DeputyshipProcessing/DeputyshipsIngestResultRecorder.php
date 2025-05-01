<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipsIngestResultRecorder
{
    private const SUCCESS_MESSAGE = 'successfully ingested deputyships CSV';

    private bool $csvLoadedSuccessfully = false;
    private bool $candidatesSelectedSuccessfully = false;

    /** @var string[] */
    private array $errorMessages = [];

    /**
     * Record the result of loading the CSV file into the staging table.
     */
    public function recordCsvLoadResult(string $fileLocation, bool $loadedOk): void
    {
        $this->csvLoadedSuccessfully = $loadedOk;

        if (!$loadedOk) {
            $this->errorMessages[] = "failed to load CSV from $fileLocation";
        }
    }

    /**
     * Record the candidate records found which will result in database activity.
     */
    public function recordDeputyshipCandidatesResult(DeputyshipCandidatesSelectorResult $result): void
    {
        $this->candidatesSelectedSuccessfully = true;

        if (!is_null($result->exception)) {
            $this->candidatesSelectedSuccessfully = false;
            $this->errorMessages[] = $result->exception->getMessage();
        }
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
        $success = $this->csvLoadedSuccessfully && $this->candidatesSelectedSuccessfully;

        $message = self::SUCCESS_MESSAGE;
        if (!$success) {
            $message = implode('; ', $this->errorMessages);
        }

        return new DeputyshipsCSVIngestResult($success, $message);
    }
}
