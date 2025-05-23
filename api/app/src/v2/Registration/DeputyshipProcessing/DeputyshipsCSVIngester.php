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
     */
    public function processCsv(string $fileLocation): DeputyshipsCSVIngestResult
    {
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
        $builderResults = $this->deputyshipBuilder->build($candidatesResult->candidates);

        // each $builderResult contains a group of court order entities and relationships to be persisted
        foreach ($builderResults as $builderResult) {
            // TODO properly log builder result
            $this->deputyshipsIngestResultRecorder->recordBuilderResult($builderResult);
        }

        $this->deputyshipsIngestResultRecorder->recordEnd();

        // get a summary of what happened during the ingest
        return $this->deputyshipsIngestResultRecorder->result();
    }
}
