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

    public function testAddingNewSingleCourtOrder(): void
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

    public function testAddingCourtOrdersWithHybridReports(): void
    {
        $hwRecord = $this->entityManager->getRepository(StagingDeputyship::class)->findOneBy(['orderUid' => '700000001107', 'deputyUid' => '700761111007']);
        $pfaRecord = $this->entityManager->getRepository(StagingDeputyship::class)->findOneBy(['orderUid' => '700000001108', 'deputyUid' => '700761111007']);

        $deputy = new Deputy();
        $deputy->setFirstname('Stuart');
        $deputy->setLastname('One');
        $deputy->setEmail1('stuart.one@test.co.uk');
        $deputy->setDeputyUid('700761111007');
        $this->entityManager->persist($deputy);

        $client = (new ClientTestHelper())->generateClient($this->entityManager, null, null, '61111004');
        $hwReport = (new ReportTestHelper())->generateReport($this->entityManager, $client, '104', new \DateTime('2019-01-21'), new \DateTime('2020-01-21'));
        $pfaReport = (new ReportTestHelper())->generateReport($this->entityManager, $client, '102', new \DateTime('2019-01-21'), new \DateTime('2020-01-21'));

        $client->addReport($hwReport);
        $client->addReport($pfaReport);

        $hwReport->setClient($client);
        $pfaReport->setClient($client);

        $this->entityManager->persist($client);
        $this->entityManager->persist($hwReport);
        $this->entityManager->persist($pfaReport);
        $this->entityManager->flush();

        $sut = new DeputyshipsCandidatesSelector($this->entityManager);
        $selectedCandidates = $sut->select();

        $this->assertEquals('INSERT ORDER', $selectedCandidates[0]->action);
        $this->assertEquals($hwRecord->orderUid, $selectedCandidates[0]->orderUid);
        $this->assertEquals($hwRecord->deputyUid, $selectedCandidates[0]->deputyUid);
        $this->assertEquals($client->getId(), $selectedCandidates[0]->clientId);

        $this->assertEquals('INSERT ORDER DEPUTY', $selectedCandidates[1]->action);
        $this->assertEquals($hwRecord->orderUid, $selectedCandidates[1]->orderUid);
        $this->assertEquals($hwRecord->deputyUid, $selectedCandidates[1]->deputyUid);
        $this->assertEquals($hwRecord->deputyStatusOnOrder, $selectedCandidates[1]->deputyStatusOnOrder);
        $this->assertEquals($deputy->getId(), $selectedCandidates[1]->deputyId);

        $this->assertEquals('INSERT ORDER REPORT', $selectedCandidates[2]->action);
        $this->assertEquals($hwRecord->orderUid, $selectedCandidates[2]->orderUid);
        $this->assertEquals($hwRecord->deputyUid, $selectedCandidates[2]->deputyUid);
        $this->assertEquals($hwReport->getId(), $selectedCandidates[2]->reportId);
        $this->assertEquals(substr($hwRecord->reportType, 3), $hwReport->getType());

        $this->assertEquals('INSERT ORDER', $selectedCandidates[3]->action);
        $this->assertEquals($pfaRecord->orderUid, $selectedCandidates[3]->orderUid);
        $this->assertEquals($pfaRecord->deputyUid, $selectedCandidates[3]->deputyUid);
        $this->assertEquals($client->getId(), $selectedCandidates[3]->clientId);

        $this->assertEquals('INSERT ORDER DEPUTY', $selectedCandidates[4]->action);
        $this->assertEquals($pfaRecord->orderUid, $selectedCandidates[4]->orderUid);
        $this->assertEquals($pfaRecord->deputyUid, $selectedCandidates[4]->deputyUid);
        $this->assertEquals($pfaRecord->deputyStatusOnOrder, $selectedCandidates[4]->deputyStatusOnOrder);
        $this->assertEquals($deputy->getId(), $selectedCandidates[4]->deputyId);

        $this->assertEquals('INSERT ORDER REPORT', $selectedCandidates[5]->action);
        $this->assertEquals($pfaRecord->orderUid, $selectedCandidates[5]->orderUid);
        $this->assertEquals($pfaRecord->deputyUid, $selectedCandidates[5]->deputyUid);
        $this->assertEquals($hwReport->getId(), $selectedCandidates[5]->reportId);
    }

    public function testAddingHistoricalAndCurrentReportsToCourtOrder(): void
    {
        $record = $this->entityManager->getRepository(StagingDeputyship::class)->findOneBy(['orderUid' => '700000001109', 'deputyUid' => '700761111008']);

        $deputy = new Deputy();
        $deputy->setFirstname('John');
        $deputy->setLastname('Snow');
        $deputy->setEmail1('martin.freeman@test.co.uk');
        $deputy->setDeputyUid('700761111008');
        $this->entityManager->persist($deputy);

        $client = (new ClientTestHelper())->generateClient($this->entityManager, null, null, '61111005');
        $historicReport = (new ReportTestHelper())->generateReport($this->entityManager, $client, '102', new \DateTime('2018-01-21'), new \DateTime('2019-01-21'))->setSubmitDate(new \DateTime('2019-01-21'))->setSubmitted(true);
        $currentReport = (new ReportTestHelper())->generateReport($this->entityManager, $client, '102', new \DateTime('2019-01-21'), new \DateTime('2020-01-21'));

        $client->addReport($historicReport);
        $client->addReport($currentReport);
        $historicReport->setClient($client);
        $currentReport->setClient($client);

        $this->entityManager->persist($client);
        $this->entityManager->persist($historicReport);
        $this->entityManager->persist($currentReport);

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
        $this->assertEquals($currentReport->getId(), $selectedCandidates[2]->reportId);
        $this->assertEquals(substr($record->reportType, 3), $currentReport->getType());

        $this->assertEquals('INSERT ORDER REPORT', $selectedCandidates[3]->action);
        $this->assertEquals($record->orderUid, $selectedCandidates[3]->orderUid);
        $this->assertEquals($record->deputyUid, $selectedCandidates[3]->deputyUid);
        $this->assertEquals($historicReport->getId(), $selectedCandidates[3]->reportId);
        $this->assertEquals(substr($record->reportType, 3), $historicReport->getType());
    }

    public function testAddingCourtOrdersWithDualReports(): void
    {
        $pfaRecord = $this->entityManager->getRepository(StagingDeputyship::class)->findOneBy(['orderUid' => '700000001105', 'deputyUid' => '700761111005']);
        $hwRecord = $this->entityManager->getRepository(StagingDeputyship::class)->findOneBy(['orderUid' => '700000001106', 'deputyUid' => '700761111006']);

        $deputy1 = new Deputy();
        $deputy1->setFirstname('Stuart');
        $deputy1->setLastname('One');
        $deputy1->setEmail1('stuart.one@test.co.uk');
        $deputy1->setDeputyUid('700761111005');
        $this->entityManager->persist($deputy1);

        $deputy2 = new Deputy();
        $deputy2->setFirstname('James');
        $deputy2->setLastname('One');
        $deputy2->setEmail1('james.one@test.co.uk');
        $deputy2->setDeputyUid('700761111006');
        $this->entityManager->persist($deputy2);

        $client = (new ClientTestHelper())->generateClient($this->entityManager, null, null, '61111003');
        $pfaReport = (new ReportTestHelper())->generateReport($this->entityManager, $client, '102', new \DateTime('2019-01-21'), new \DateTime('2020-01-21'));
        $hwReport = (new ReportTestHelper())->generateReport($this->entityManager, $client, '104', new \DateTime('2019-01-21'), new \DateTime('2020-01-21'));

        $client->addReport($pfaReport);
        $client->addReport($hwReport);
        $pfaReport->setClient($client);
        $hwReport->setClient($client);

        $this->entityManager->persist($client);
        $this->entityManager->persist($pfaReport);
        $this->entityManager->persist($hwReport);

        $this->entityManager->flush();

        $sut = new DeputyshipsCandidatesSelector($this->entityManager);

        $selectedCandidates = $sut->select();

        $this->assertEquals('INSERT ORDER', $selectedCandidates[0]->action);
        $this->assertEquals($pfaRecord->orderUid, $selectedCandidates[0]->orderUid);
        $this->assertEquals($pfaRecord->deputyUid, $selectedCandidates[0]->deputyUid);
        $this->assertEquals($pfaRecord->orderType, $selectedCandidates[0]->orderType);
        $this->assertEquals($client->getId(), $selectedCandidates[0]->clientId);

        $this->assertEquals('INSERT ORDER DEPUTY', $selectedCandidates[1]->action);
        $this->assertEquals($pfaRecord->orderUid, $selectedCandidates[1]->orderUid);
        $this->assertEquals($pfaRecord->deputyUid, $selectedCandidates[1]->deputyUid);
        $this->assertEquals($pfaRecord->deputyStatusOnOrder, $selectedCandidates[1]->deputyStatusOnOrder);
        $this->assertEquals($deputy1->getId(), $selectedCandidates[1]->deputyId);

        $this->assertEquals('INSERT ORDER REPORT', $selectedCandidates[2]->action);
        $this->assertEquals($pfaRecord->orderUid, $selectedCandidates[2]->orderUid);
        $this->assertEquals($pfaRecord->deputyUid, $selectedCandidates[2]->deputyUid);
        $this->assertEquals($pfaReport->getId(), $selectedCandidates[2]->reportId);

        $this->assertEquals('INSERT ORDER', $selectedCandidates[3]->action);
        $this->assertEquals($hwRecord->orderUid, $selectedCandidates[3]->orderUid);
        $this->assertEquals($hwRecord->deputyUid, $selectedCandidates[3]->deputyUid);
        $this->assertEquals($hwRecord->orderType, $selectedCandidates[3]->orderType);
        $this->assertEquals($client->getId(), $selectedCandidates[3]->clientId);

        $this->assertEquals('INSERT ORDER DEPUTY', $selectedCandidates[4]->action);
        $this->assertEquals($hwRecord->orderUid, $selectedCandidates[4]->orderUid);
        $this->assertEquals($hwRecord->deputyUid, $selectedCandidates[4]->deputyUid);
        $this->assertEquals($hwRecord->deputyStatusOnOrder, $selectedCandidates[4]->deputyStatusOnOrder);
        $this->assertEquals($deputy2->getId(), $selectedCandidates[4]->deputyId);

        $this->assertEquals('DUAL ORDER FOUND', $selectedCandidates[5]->action);
        $this->assertEquals($hwRecord->orderUid, $selectedCandidates[5]->orderUid);
        $this->assertEquals($hwRecord->deputyUid, $selectedCandidates[5]->deputyUid);
        $this->assertEquals($client->getId(), $selectedCandidates[5]->clientId);
        $this->assertEquals($hwReport->getId(), $selectedCandidates[5]->reportId);
    }

    // test adding Historical And Current Hybrid Reports To CourtOrder
}
