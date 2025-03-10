<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\v2\CSV\CSVChunkerFactory;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\Mapping\MappingException;
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

    public function load(string $fileLocation): bool
    {
        $conn = $this->em->getConnection();
        $stagingDeputyshipTableName = $this->em->getClassMetadata(StagingDeputyship::class)->getTableName();

        try {
            // delete records from staging table
            $this->em->beginTransaction();
            $conn->executeStatement("DELETE FROM {$stagingDeputyshipTableName}");
            $this->em->commit();

            // dump CSV into staging table
            $chunker = $this->chunkerFactory->create($fileLocation, StagingDeputyship::class);

            $this->em->beginTransaction();
            while (!is_null($chunk = $chunker->getChunk())) {
                foreach ($chunk as $record) {
                    $this->em->persist($record);
                }
                $this->em->flush();
            }
            $this->em->commit();
            $this->em->clear();

            return true;
        } catch (ORMException|MappingException|Exception|UnavailableStream|CSVException $e) {
            $this->logger->error(
                'Error loading CSV into staging table: exception type = '.get_class($e).'; message = '.$e->getMessage()
            );
            $this->em->rollback();

            return false;
        }
    }
}
