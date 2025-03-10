<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\DTO\DeputyshipRowDto;
use App\v2\Registration\Enum\DeputyshipProcessingStatus;
use League\Csv\Reader;

/**
 * Ingest the deputyship CSV exported from Sirius.
 */
class DeputyshipsCSVIngester
{
    public function __construct(
        private readonly DeputyshipEntityMatcher $deputyshipEntityMatcher,
        private readonly DeputyshipBuilder $deputyshipBuilder,
        private readonly DeputyshipPersister $deputyshipPersister,
        private readonly DeputyshipsIngestResultRecorder $deputyshipIngestResultRecorder,
    ) {
    }

    /**
     * Process the CSV file through the reader $csvFile.
     */
    public function processCsv(Reader $csvFile): DeputyshipsCSVIngestResult
    {
        /** @var DeputyshipRowDto $deputyShipRowDto */
        foreach ($csvFile->getRecordsAsObject(DeputyshipRowDto::class) as $deputyShipRowDto) {
            $state = new DeputyshipPipelineState($deputyShipRowDto);

            // find existing deputy, client, and report which match the row
            $state = $this->deputyshipEntityMatcher->match($state);

            // build the entities without saving them
            $state = $this->deputyshipBuilder->build($state);

            // persist the entities to the database
            $state = $this->deputyshipPersister->persist($state);

            $status = $state->status;
            if (DeputyshipProcessingStatus::SKIPPED === $status) {
                $this->deputyshipIngestResultRecorder->recordSkippedRow($state);
            } elseif (DeputyshipProcessingStatus::FAILED === $status) {
                $this->deputyshipIngestResultRecorder->recordFailedRow($state);
            } elseif (DeputyshipProcessingStatus::SUCCEEDED == $status) {
                $this->deputyshipIngestResultRecorder->recordProcessedRow($state);
            }
        }

        return $this->deputyshipIngestResultRecorder->result();
    }
}
