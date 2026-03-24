<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Factory\DataFactoryInterface;
use App\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipIngester;
use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * Ingest the deputyship CSV exported from Sirius.
 */
final readonly class DeputyshipsCSVIngester
{
    public function __construct(
        private DeputyshipsCSVLoader $deputyshipsCSVLoader,
        private DeputyshipsCandidatesSelector $deputyshipsCandidatesSelector,
        private DeputyshipBuilder $deputyshipBuilder,
        private DataFactoryInterface $preCSVDataFactory,
        private DataFactoryInterface $postCSVDataFactory,
        private DeputyshipsIngestResultRecorder $deputyshipsIngestResultRecorder,
        private CourtOrderRelationshipIngester $courtOrderRelationshipIngester,
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

        // apply manual data fixes before CSV ingested
        $dataFactoryResult = $this->preCSVDataFactory->run();
        $this->deputyshipsIngestResultRecorder->recordPreCSVDataFactoryResult($dataFactoryResult);
        if (!$dataFactoryResult->isSuccessful()) {
            return $this->deputyshipsIngestResultRecorder->result();
        }

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

        // update CourtOrder relationships and kinds
        if (!$dryRun) {
            $relationshipResults = $this->courtOrderRelationshipIngester->execute();
            foreach ($relationshipResults as $relationshipResult) {
                $this->deputyshipsIngestResultRecorder->recordRelationshipResult($relationshipResult);
            }
        }

        // apply manual data fixes after CSV ingested
        $dataFactoryResult = $this->postCSVDataFactory->run();
        $this->deputyshipsIngestResultRecorder->recordPostCSVDataFactoryResult($dataFactoryResult);
        if (!$dataFactoryResult->isSuccessful()) {
            return $this->deputyshipsIngestResultRecorder->result();
        }

        $this->deputyshipsIngestResultRecorder->recordEnd();

        // get a summary of what happened during the ingest
        return $this->deputyshipsIngestResultRecorder->result();
    }
}
