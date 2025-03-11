<?php

declare(strict_types=1);

namespace app\tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\v2\CSV\CSVChunkerFactory;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyshipsCSVLoaderIntegrationTest extends KernelTestCase
{
    private EntityManager $entityManager;
    private CSVChunkerFactory $chunkerFactory;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $container = self::bootKernel()->getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();
        $this->chunkerFactory = new CSVChunkerFactory();
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new ORMPurger($this->entityManager))->purge();
    }

    /**
     * @throws UnavailableStream
     * @throws NotSupported
     * @throws Exception
     */
    public function testLoadSuccess(): void
    {
        $fileLocation = dirname(__FILE__).'/../../../../csv/(DigiDeps)_Deputyships_Report.csv';

        // count lines in the CSV file, minus the header; we expect one record in the StagingDeputyship table for
        // each row in the input CSV
        $reader = Reader::createFromPath($fileLocation);
        $reader->setHeaderOffset(0);
        $numRecords = $reader->count();

        $sut = new DeputyshipsCSVLoader($this->entityManager, $this->chunkerFactory, $this->logger);

        $loadOk = $sut->load($fileLocation);

        self::assertTrue($loadOk);

        $records = $this->entityManager->getRepository(StagingDeputyship::class)->findAll();
        self::assertCount($numRecords, $records);
    }
}
