<?php

declare(strict_types=1);

namespace app\tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Entity\Deputy;
use App\Entity\StagingDeputyship;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
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
    public function testCourtOrderStatusChange(): void
    {
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
        $this->assertEquals('UPDATE ORDER STATUS', $selectedCandidates[0]->action);
        $this->assertEquals('ACTIVE', $selectedCandidates[0]->status);
    }

    public function testDeputyStatusChangeOnCourtOrder(): void
    {
        $courtOrder = new CourtOrder();
        $courtOrderUid = '700000001102';

        $courtOrder->setCourtOrderUid($courtOrderUid);
        $courtOrder->setOrderType('hw');
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new \DateTime('2019-01-21'));
        $this->entityManager->persist($courtOrder);

        $deputy = new Deputy();
        $deputy->setFirstname('John');
        $deputy->setLastname('Snow');
        $deputy->setEmail1('john.snow@test.co.uk');
        $deputy->setDeputyUid('700761111002');
        $this->entityManager->persist($deputy);

        $deputyOnCourtOrder = new CourtOrderDeputy();
        $deputyOnCourtOrder->setCourtOrder($courtOrder);
        $deputyOnCourtOrder->setDeputy($deputy);
        $deputyOnCourtOrder->setIsActive(true);
        $this->entityManager->persist($deputyOnCourtOrder);

        $this->entityManager->flush();

        $sut = new DeputyshipsCandidatesSelector($this->entityManager);

        $selectedCandidates = $sut->select();
        $this->assertEquals('UPDATE DEPUTY STATUS ON ORDER', $selectedCandidates[0]->action);
        $this->assertFalse($selectedCandidates[0]->deputyStatusOnOrder);
    }

    public function testNewDeputyAddedToCourtOrder(): void
    {
        $courtOrder = new CourtOrder();
        $courtOrderUid = '700000001103';

        $courtOrder->setCourtOrderUid($courtOrderUid);
        $courtOrder->setOrderType('hw');
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new \DateTime('2019-01-21'));
        $this->entityManager->persist($courtOrder);

        $deputy = new Deputy();
        $deputy->setFirstname('John');
        $deputy->setLastname('Snow');
        $deputy->setEmail1('john.snow@test.co.uk');
        $deputy->setDeputyUid('700761111003');
        $this->entityManager->persist($deputy);

        $this->entityManager->flush();

        $sut = new DeputyshipsCandidatesSelector($this->entityManager);

        $selectedCandidates = $sut->select();
        $this->assertEquals('INSERT ORDER DEPUTY', $selectedCandidates[0]->action);
        $this->assertEquals($courtOrder->getCourtOrderUid(), $selectedCandidates[0]->orderUid);
        $this->assertEquals($deputy->getDeputyUid(), $selectedCandidates[0]->deputyUid);
    }

    public function testAddingInNewCourtOrder(): void
    {
        $record = $this->entityManager->getRepository(StagingDeputyship::class)->findOneBy(['orderUid' => '700000001104', 'deputyUid' => '700761111004']);

        $deputy = new Deputy();
        $deputy->setFirstname('Stuart');
        $deputy->setLastname('One');
        $deputy->setEmail1('stuart.one@test.co.uk');
        $deputy->setDeputyUid('700761111004');
        $this->entityManager->persist($deputy);

        $client = (new ClientTestHelper())->generateClient($this->entityManager, null, null, '61111002');
        $report = (new ReportTestHelper())->generateReport($this->entityManager, $client, '104', new \DateTime('2019-01-21'), new \DateTime('2020-01-21'));

        $client->addReport($report);
        $report->setClient($client);

        $this->entityManager->persist($client);
        $this->entityManager->persist($report);

        $this->entityManager->flush();

        $sut = new DeputyshipsCandidatesSelector($this->entityManager);

        $selectedCandidates = $sut->select();

        $this->assertEquals('INSERT ORDER', $selectedCandidates[0]->action);
        $this->assertEquals($record->orderUid, $selectedCandidates[0]->orderUid);
        $this->assertEquals($record->deputyUid, $selectedCandidates[0]->deputyUid);
        $this->assertEquals($client->getId(), $selectedCandidates[0]->clientId);

        $this->assertEquals('INSERT ORDER DEPUTY', $selectedCandidates[1]->action);
        $this->assertEquals($record->orderUid, $selectedCandidates[1]->orderUid);
        $this->assertEquals($record->deputyUid, $selectedCandidates[1]->deputyUid);
        $this->assertEquals($record->deputyStatusOnOrder, $selectedCandidates[1]->deputyStatusOnOrder);
        $this->assertEquals($deputy->getId(), $selectedCandidates[1]->deputyId);

        $this->assertEquals('INSERT ORDER REPORT', $selectedCandidates[2]->action);
        $this->assertEquals($record->orderUid, $selectedCandidates[2]->orderUid);
        $this->assertEquals($record->deputyUid, $selectedCandidates[2]->deputyUid);
        $this->assertEquals($report->getId(), $selectedCandidates[2]->reportId);
        $this->assertEquals(substr($record->reportType, 3), $report->getType());
    }
}
