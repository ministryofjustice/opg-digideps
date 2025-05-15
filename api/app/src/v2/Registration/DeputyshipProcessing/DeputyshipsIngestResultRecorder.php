<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use Psr\Log\LoggerInterface;

class DeputyshipsIngestResultRecorder
{
    private const SUCCESS_MESSAGE = 'successfully ingested deputyships CSV';

    private bool $csvLoadedSuccessfully = false;
    private bool $candidatesSelectedSuccessfully = false;

    /** @var string[] */
    private array $errorMessages = [];

    /** @var string[] */
    private array $messages = [];

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    private function logMessage(string $message)
    {
        $this->messages[] = $message;
        $this->logger->info($message);
        error_log('+++++++++++++++ RR: '.$message);
    }

    private function logError(string $errorMessage)
    {
        $this->errorMessages[] = $errorMessage;
        $this->logger->error($errorMessage);
        error_log('!!!!!!!!!!!!!!!! RR ERROR: '.$errorMessage);
    }

    /**
     * Record the result of loading the CSV file into the staging table.
     */
    public function recordCsvLoadResult(string $fileLocation, bool $loadedOk): void
    {
        $this->csvLoadedSuccessfully = $loadedOk;

        if ($loadedOk) {
            $this->logMessage("loaded deputyships CSV from $fileLocation");
        } else {
            $this->logError("failed to load deputyships CSV from $fileLocation");
        }
    }

    /**
     * Record the candidate records found which will result in database activity.
     */
    public function recordDeputyshipCandidatesResult(DeputyshipCandidatesSelectorResult $result): void
    {
        $this->candidatesSelectedSuccessfully = true;

        if (is_null($result->exception)) {
            $this->logMessage("found $result->numCandidates candidate database updates");
        } else {
            $this->candidatesSelectedSuccessfully = false;
            $this->logError($result->exception->getMessage());
        }
    }

    public function result(): DeputyshipsCSVIngestResult
    {
        // note that we don't count builder errors towards the overall success of the ingest
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
