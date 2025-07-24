<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\Tests\Integration\ApiBaseTestCase;
use App\v2\CSV\CSVChunkerFactory;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use Doctrine\ORM\Exception\NotSupported;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Psr\Log\LoggerInterface;

class DeputyshipsCSVLoaderIntegrationTest extends ApiBaseTestCase
{
    private CSVChunkerFactory $chunkerFactory;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chunkerFactory = new CSVChunkerFactory();
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @throws UnavailableStream
     * @throws NotSupported
     * @throws Exception
     */
    public function testLoadSuccess(): void
    {
        $fileLocation = dirname(__FILE__).'/../../../../csv/deputyshipsReport.csv';

        // count lines in the CSV file, minus the header; we expect one record in the StagingDeputyship table for
        // each row in the input CSV
        $reader = Reader::createFromPath($fileLocation);
        $reader->setHeaderOffset(0);
        $numRecords = $reader->count();

        $sut = new DeputyshipsCSVLoader($this->entityManager, $this->chunkerFactory, $this->logger);

        $loadResult = $sut->load($fileLocation);

        self::assertTrue($loadResult->loadedOk);
        self::assertEquals($fileLocation, $loadResult->fileLocation);
        self::assertEquals($numRecords, $loadResult->numRecords);

        $records = $this->entityManager->getRepository(StagingDeputyship::class)->findAll();
        self::assertCount($numRecords, $records);
    }
}
