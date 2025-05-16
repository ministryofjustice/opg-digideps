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

    private \DateTimeImmutable $startDateTime;

    private \DateTimeImmutable $endDateTime;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    private function formatDate(\DateTimeImmutable $dateTime): string
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    private function debugLog(string $message): void
    {
        $this->logger->debug($this->formatDate(new \DateTimeImmutable()).' '.$message);

        $memMessage = '******** PEAK MEMORY USAGE = '.floor(memory_get_peak_usage(true) / pow(1024, 2)).'M';
        $this->logger->debug($memMessage);
    }

    private function logMessage(string $message): void
    {
        $this->messages[] = $message;
        $this->logger->info($message);
        $this->debugLog('++++++++ INFO: '.$message);
    }

    private function logError(string $errorMessage): void
    {
        $this->errorMessages[] = $errorMessage;
        $this->logger->error($errorMessage);
        $this->debugLog('!!!!!!!!!!!!!!!! ERROR: '.$errorMessage);
    }

    public function recordStart(\DateTimeImmutable $startDateTime = new \DateTimeImmutable()): void
    {
        $this->startDateTime = $startDateTime;
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

    public function recordBuilderResult(DeputyshipBuilderResult $builderResult): void
    {
        $this->debugLog('++++++++ '.$builderResult->getMessage());
    }

    public function recordEnd(\DateTimeImmutable $endDateTime = new \DateTimeImmutable()): void
    {
        $this->endDateTime = $endDateTime;
    }

    public function result(): DeputyshipsCSVIngestResult
    {
        // note that we don't count builder errors towards the overall success of the ingest
        $success = $this->csvLoadedSuccessfully && $this->candidatesSelectedSuccessfully;

        $message = 'Ingest started at: '.$this->formatDate($this->startDateTime).
            '; ended at: '.$this->formatDate($this->endDateTime).
            '; execution time: '.$this->endDateTime->diff($this->startDateTime)->format('%hh %im %ss');

        $message .= ' --- '.implode('; ', $this->messages);

        if ($success) {
            $message .= '; '.self::SUCCESS_MESSAGE;
        } else {
            $message .= implode('; ERRORS: ', $this->errorMessages);
        }

        $this->logMessage($message);

        return new DeputyshipsCSVIngestResult($success, $message);
    }
}
