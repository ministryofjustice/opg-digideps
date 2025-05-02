<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\Enum\DeputyshipProcessingStatus;

/**
 * Ingest the deputyship CSV exported from Sirius.
 */
class DeputyshipsCSVIngester
{
    public function __construct(
        private readonly DeputyshipsCSVLoader $deputyshipsCSVLoader,
        private readonly DeputyshipsCandidatesSelector $deputyshipsCandidatesSelector,
        private readonly DeputyshipBuilder $deputyshipBuilder,
        private readonly DeputyshipPersister $deputyshipPersister,
        private readonly DeputyshipsIngestResultRecorder $deputyshipsIngestResultRecorder,
    ) {
    }

    /**
     * Process the CSV file at $fileLocation.
     */
    public function processCsv(string $fileLocation): DeputyshipsCSVIngestResult
    {
        // load the CSV into the staging table in the database
        $loadedOk = $this->deputyshipsCSVLoader->load($fileLocation);
        $this->deputyshipsIngestResultRecorder->recordCsvLoadResult($fileLocation, $loadedOk);
        if (!$loadedOk) {
            return $this->deputyshipsIngestResultRecorder->result();
        }

        // find the candidate deputyships which have changed or need to be added
        $candidatesResult = $this->deputyshipsCandidatesSelector->select();
        $this->deputyshipsIngestResultRecorder->recordDeputyshipCandidatesResult($candidatesResult);
        if (!$candidatesResult->success()) {
            return $this->deputyshipsIngestResultRecorder->result();
        }

        // TODO order the candidates before processing them in the loop below
        foreach ($candidatesResult->candidates as $candidate) {
            // build the CourtOrder and related entities from the candidate
            $state = $this->deputyshipBuilder->build($candidate);

            // persist the entities to the database (NB this could be chunked within the persister)
            $state = $this->deputyshipPersister->persist($state);

            // record what happened for later logging
            $status = $state->status;
            if (DeputyshipProcessingStatus::SKIPPED === $status) {
                $this->deputyshipsIngestResultRecorder->recordSkippedRow($state);
            } elseif (DeputyshipProcessingStatus::FAILED === $status) {
                $this->deputyshipsIngestResultRecorder->recordFailedRow($state);
            } elseif (DeputyshipProcessingStatus::SUCCEEDED == $status) {
                $this->deputyshipsIngestResultRecorder->recordProcessedRow($state);
            }
        }

        // get a summary of what happened during the ingest
        return $this->deputyshipsIngestResultRecorder->result();
    }
}
