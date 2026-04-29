<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\v2\Registration\DeputyshipProcessing;

use OPG\Digideps\Backend\Entity\StagingDeputyship;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use OPG\Digideps\Backend\v2\CSV\CSVChunkerFactory;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use Doctrine\ORM\Exception\NotSupported;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Psr\Log\LoggerInterface;

class DeputyshipsCSVLoaderIntegrationIntegrationTest extends ApiIntegrationTestCase
{
    private static CSVChunkerFactory $chunkerFactory;
    private static LoggerInterface $logger;

    public function setUp(): void
    {
        self::$chunkerFactory = new CSVChunkerFactory();
        self::$logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @throws UnavailableStream
     * @throws NotSupported
     * @throws Exception
     */
    public function testLoadSuccess(): void
    {
        $fileLocation = dirname(__FILE__) . '/../../../../csv/deputyshipsReport.csv';

        // count lines in the CSV file, minus the header; we expect one record in the StagingDeputyship table for
        // each row in the input CSV
        $reader = Reader::from($fileLocation);
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
