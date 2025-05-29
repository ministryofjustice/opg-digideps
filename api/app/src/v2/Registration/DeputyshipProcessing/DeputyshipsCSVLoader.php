<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\v2\CSV\CSVChunkerFactory;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception as CSVException;
use League\Csv\UnavailableStream;
use Psr\Log\LoggerInterface;

class DeputyshipsCSVLoader
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CSVChunkerFactory $chunkerFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function load(string $fileLocation): DeputyshipsCSVLoaderResult
    {
        try {
            $chunker = $this->chunkerFactory->create($fileLocation, StagingDeputyship::class);

            // delete records from staging table
            $this->em->beginTransaction();
            $this->em->createQuery('DELETE FROM App\Entity\StagingDeputyship sd')->execute();
            $this->em->flush();
            $this->em->commit();

            // dump CSV into staging table
            $this->em->beginTransaction();

            $numRecords = 0;
            while (!is_null($chunk = $chunker->getChunk())) {
                $numRecords += count($chunk);
                foreach ($chunk as $record) {
                    $this->em->persist($record);
                }
                $this->em->flush();
                $this->em->clear();
            }

            $this->em->commit();

            return new DeputyshipsCSVLoaderResult(fileLocation: $fileLocation, loadedOk: true, numRecords: $numRecords);
        } catch (UnavailableStream|CSVException $e) {
            $this->logger->error(
                'Error loading CSV into staging table: exception type = '.get_class($e).'; message = '.$e->getMessage()
            );
            $this->em->rollback();

            return new DeputyshipsCSVLoaderResult(fileLocation: $fileLocation, loadedOk: false);
        }
    }
}
