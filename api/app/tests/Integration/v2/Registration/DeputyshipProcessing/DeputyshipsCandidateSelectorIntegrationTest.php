<?php

declare(strict_types=1);

namespace app\tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\CourtOrder;
use App\Entity\StagingDeputyship;
use App\v2\CSV\CSVChunkerFactory;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use League\Csv\Exception;
use League\Csv\UnavailableStream;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyshipsCandidateSelectorIntegrationTest extends KernelTestCase
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

        $fileLocation = dirname(__FILE__).'/../../../../csv/deputyshipsReport2.csv';

        $csvLoader = new DeputyshipsCSVLoader($this->entityManager, $this->chunkerFactory, $this->logger);

        $csvLoader->load($fileLocation);
    }

    //    protected function tearDown(): void
    //    {
    //        parent::tearDown();
    //
    //        (new ORMPurger($this->entityManager))->purge();
    //    }

    /**
     * @throws UnavailableStream
     * @throws NotSupported
     * @throws Exception
     */
    public function testCourtOrderStatusChange(): void
    {
        $records = $this->entityManager->getRepository(StagingDeputyship::class)->findAll();

        $courtOrder = new CourtOrder();
        $courtOrderUid = '700000001101';

        $courtOrder->setCourtOrderUid($courtOrderUid);
        $courtOrder->setOrderType('pfa');
        $courtOrder->setStatus('OPEN');
        $courtOrder->setOrderMadeDate(new \DateTime('2018-01-21'));
        $this->entityManager->persist($courtOrder);
        $this->entityManager->flush();

        $sut = new DeputyshipsCandidatesSelector($this->entityManager);

        $selectedCandidates = $sut->select();
        $this->assertEquals('ACTIVE', $selectedCandidates[0]->status);
    }
}
