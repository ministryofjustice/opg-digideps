<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

class DeputyshipsIngestResultRecorder
{
    private const SUCCESS_MESSAGE = 'successfully ingested deputyships CSV';

    private bool $csvLoadedSuccessfully = false;
    private bool $candidatesSelectedSuccessfully = false;

    // for storing builder counts
    private int $numCandidatesApplied = 0;
    private int $numCandidatesFailed = 0;

    /** @var string[] */
    private array $errorMessages = [];

    /** @var string[] */
    private array $messages = [];

    private ?\DateTimeImmutable $startDateTime = null;

    private ?\DateTimeImmutable $endDateTime = null;

    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    private function formatDate(\DateTimeImmutable $dateTime): string
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    private function formatMessage(string $message): string
    {
        return $this->formatDate(new \DateTimeImmutable()).' '.$message;
    }

    private function logMemory(): void
    {
        $memMessage = '******** PEAK MEMORY USAGE = '.floor(memory_get_peak_usage(true) / pow(1024, 2)).'M';
        $this->logger->debug($this->formatMessage($memMessage));
    }

    private function logMessage(string $message): void
    {
        $this->messages[] = $message;
        $this->logger->info($this->formatMessage($message));
        $this->logMemory();
    }

    private function logError(string $errorMessage): void
    {
        $this->errorMessages[] = $errorMessage;
        $this->logger->error($this->formatMessage($errorMessage));
        $this->logMemory();
    }

    public function recordStart(\DateTimeImmutable $startDateTime = new \DateTimeImmutable()): void
    {
        $this->startDateTime = $startDateTime;
    }

    /**
     * Record the result of loading the CSV file into the staging table.
     */
    public function recordCsvLoadResult(DeputyshipsCSVLoaderResult $result): void
    {
        $this->csvLoadedSuccessfully = $result->loadedOk;

        if ($result->loadedOk) {
            $this->logMessage("loaded $result->numRecords deputyships from CSV file $result->fileLocation");
        } else {
            $this->logError("failed to load deputyships CSV from $result->fileLocation");
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
        $this->numCandidatesApplied += $builderResult->getNumCandidatesApplied();
        $this->numCandidatesFailed += $builderResult->getNumCandidatesFailed();
        $this->logger->debug($this->formatMessage('++++++++ '.$builderResult->getMessage()));
        $this->logMemory();
    }

    public function recordEnd(\DateTimeImmutable $endDateTime = new \DateTimeImmutable()): void
    {
        $this->endDateTime = $endDateTime;
    }

    public function result(): DeputyshipsCSVIngestResult
    {
        // note that we don't count builder errors towards the overall success of the ingest
        $success = $this->csvLoadedSuccessfully && $this->candidatesSelectedSuccessfully;

        if (is_null($this->startDateTime) || is_null($this->endDateTime)) {
            $message = 'Ingest timings not available - incomplete start/end datetimes';
        } else {
            $message = 'Ingest started at: '.$this->formatDate($this->startDateTime).
                '; ended at: '.$this->formatDate($this->endDateTime).
                '; execution time: '.$this->endDateTime->diff($this->startDateTime)->format('%hh %im %ss');
        }

        $message .= ' --- '.implode('; ', $this->messages);

        $message .= '; number of candidates applied = '.$this->numCandidatesApplied.
            '; number of candidates failed = '.$this->numCandidatesFailed;

        if ($success) {
            $message .= '; '.self::SUCCESS_MESSAGE;
        } else {
            $message .= '; ERRORS: '.implode(' / ', $this->errorMessages);
        }

        $this->logMessage($message);

        return new DeputyshipsCSVIngestResult($success, $message);
    }

    public function setLogger(ConsoleLogger $logger): void
    {
        $this->logger = $logger;
    }
}
