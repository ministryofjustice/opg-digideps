<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\Tests\Integration\ApiTestCase;
use App\v2\CSV\CSVChunkerFactory;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use League\Csv\Reader;
use Psr\Log\LoggerInterface;

class DeputyshipsCSVLoaderIntegrationTest extends ApiTestCase
{
    private static CSVChunkerFactory $chunkerFactory;
    private static LoggerInterface $logger;

    public function setUp(): void
    {
        self::$chunkerFactory = new CSVChunkerFactory();
        self::$logger = $this->createMock(LoggerInterface::class);
    }

    public function testLoadSuccess(): void
    {
        $fileLocation = dirname(__FILE__).'/../../../../csv/deputyshipsReport.csv';

        // count lines in the CSV file, minus the header; we expect one record in the StagingDeputyship table for
        // each row in the input CSV
        $reader = Reader::createFromPath($fileLocation);
        $reader->setHeaderOffset(0);
        $numRecords = $reader->count();

        $sut = new DeputyshipsCSVLoader(self::$entityManager, self::$chunkerFactory, self::$logger);

        $loadResult = $sut->load($fileLocation);

        self::assertTrue($loadResult->loadedOk);
        self::assertEquals($fileLocation, $loadResult->fileLocation);
        self::assertEquals($numRecords, $loadResult->numRecords);

        $records = self::$entityManager->getRepository(StagingDeputyship::class)->findAll();
        self::assertCount($numRecords, $records);
    }
}
