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

    /** @var string[] */
    private array $messages = [];

    /**
     * Record the result of loading the CSV file into the staging table.
     */
    public function recordCsvLoadResult(string $fileLocation, bool $loadedOk): void
    {
        $this->csvLoadedSuccessfully = $loadedOk;

        if ($loadedOk) {
            $this->messages[] = "loaded deputyships CSV from $fileLocation";
        } else {
            $this->errorMessages[] = "failed to load deputyships CSV from $fileLocation";
        }
    }

    /**
     * Record the candidate records found which will result in database activity.
     */
    public function recordDeputyshipCandidatesResult(DeputyshipCandidatesSelectorResult $result): void
    {
        $this->candidatesSelectedSuccessfully = true;

        if (is_null($result->exception)) {
            $this->messages[] = "found {$result->numCandidates} candidate database updates";
        } else {
            $this->candidatesSelectedSuccessfully = false;
            $this->errorMessages[] = $result->exception->getMessage();
        }
    }

    public function result(): DeputyshipsCSVIngestResult
    {
        $success = $this->csvLoadedSuccessfully && $this->candidatesSelectedSuccessfully;

        $message = implode('; ', $this->messages);
        if ($success) {
            $message .= '; '.self::SUCCESS_MESSAGE;
        } else {
            $message .= implode('; ERRORS: ', $this->errorMessages);
        }

        return new DeputyshipsCSVIngestResult($success, $message);
    }

    public function recordBuilderResult(DeputyshipBuilderResult $builderResult): void
    {
    }

    public function recordPersisterResult(DeputyshipPersisterResult $persisterResult): void
    {
    }
}
