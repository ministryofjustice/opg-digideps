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

    private ?\DateTimeInterface $startDateTime = null;

    private ?\DateTimeInterface $endDateTime = null;

    private bool $dryRun = false;

    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    private function formatDate(\DateTimeInterface $dateTime): string
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    private function formatMessage(string $message): string
    {
        return $this->formatDate(new \DateTimeImmutable()).' deputyships-ingest '.$message;
    }

    private function logMessage(string $message): void
    {
        $this->messages[] = $message;
        $this->logger->warning($this->formatMessage($message));
    }

    private function logError(string $errorMessage): void
    {
        $this->errorMessages[] = $errorMessage;

        if ($this->dryRun) {
            $this->logger->warning('{ERROR if not dry run} - '.$this->formatMessage($errorMessage));
        } else {
            $this->logger->error($this->formatMessage($errorMessage));
        }
    }

    public function recordStart(\DateTimeInterface $startDateTime = new \DateTimeImmutable()): void
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

        // these messages are not output with logMessage() or logError() because there will be a lot of them
        $this->logMessage('++++++++ '.$builderResult->getMessage());

        $errorMessage = $builderResult->getErrorMessage();
        if (!is_null($errorMessage)) {
            $this->logError('!!!!!!! '.$errorMessage);
        }
    }

    public function recordEnd(\DateTimeInterface $endDateTime = new \DateTimeImmutable()): void
    {
        $this->endDateTime = $endDateTime;
    }

    public function result(): DeputyshipsCSVIngestResult
    {
        $memMessage = 'PEAK MEMORY USAGE = '.floor(memory_get_peak_usage(true) / pow(1024, 2)).'M';
        $this->logger->warning($this->formatMessage($memMessage));

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

    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }
}
