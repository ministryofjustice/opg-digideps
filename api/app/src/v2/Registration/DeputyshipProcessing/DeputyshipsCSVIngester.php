<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * Ingest the deputyship CSV exported from Sirius.
 */
class DeputyshipsCSVIngester
{
    public function __construct(
        private readonly DeputyshipsCSVLoader $deputyshipsCSVLoader,
        private readonly DeputyshipsCandidatesSelector $deputyshipsCandidatesSelector,
        private readonly DeputyshipBuilder $deputyshipBuilder,
        private readonly DeputyshipsIngestResultRecorder $deputyshipsIngestResultRecorder,
    ) {
    }

    public function setLogger(ConsoleLogger $logger): void
    {
        $this->deputyshipsIngestResultRecorder->setLogger($logger);
    }

    /**
     * Process the CSV file at $fileLocation.
     *
     * If $dryRun is true, the full CSV process is applied, including creating court orders and relationships,
     * but none of the court order data are saved to the database (all transactions are rolled back). The
     * deputyship and selectedcandidates tables will still be populated.
     */
    public function processCsv(string $fileLocation, bool $dryRun = false): DeputyshipsCSVIngestResult
    {
        $this->deputyshipsIngestResultRecorder->setDryRun($dryRun);
        $this->deputyshipsIngestResultRecorder->recordStart();

        // load the CSV into the staging table in the database
        $loadResult = $this->deputyshipsCSVLoader->load($fileLocation);
        $this->deputyshipsIngestResultRecorder->recordCsvLoadResult($loadResult);
        if (!$loadResult->loadedOk) {
            return $this->deputyshipsIngestResultRecorder->result();
        }

        // find the candidate deputyships which have changed or need to be added
        $candidatesResult = $this->deputyshipsCandidatesSelector->select();
        $this->deputyshipsIngestResultRecorder->recordDeputyshipCandidatesResult($candidatesResult);
        if (!$candidatesResult->success()) {
            return $this->deputyshipsIngestResultRecorder->result();
        }

        // create CourtOrder and related entities in groups, grouped by court order UID
        $builderResults = $this->deputyshipBuilder->build($candidatesResult->candidates, dryRun: $dryRun);

        // each $builderResult contains a group of court order entities and relationships to be persisted
        foreach ($builderResults as $builderResult) {
            $this->deputyshipsIngestResultRecorder->recordBuilderResult($builderResult);
        }

        $this->deputyshipsIngestResultRecorder->recordEnd();

        // get a summary of what happened during the ingest
        return $this->deputyshipsIngestResultRecorder->result();
    }
}
