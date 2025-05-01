<?php

declare(strict_types=1);

namespace app\tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\StagingDeputyship;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyshipsCandidatesSelectorIntegrationTest extends KernelTestCase
{
    private EntityManager $entityManager;
    private DeputyshipsCandidatesSelector $sut;

    protected function setUp(): void
    {
        $container = self::bootKernel()->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        $fileLocation = dirname(__FILE__).'/../../../../csv/deputyshipsReport2.csv';

        $csvLoader = $container->get(DeputyshipsCSVLoader::class);
        $csvLoader->load($fileLocation);

        /** @var ?DeputyshipsCandidatesSelector $sut */
        $sut = $container->get(DeputyshipsCandidatesSelector::class);
        $this->sut = $sut;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        (new ORMPurger($this->entityManager))->purge();
    }

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

        $selectedCandidates = $this->sut->select()->candidates;

        static::assertEquals('UPDATE ORDER STATUS', $selectedCandidates[0]->action);
        static::assertEquals('ACTIVE', $selectedCandidates[0]->status);
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

        $deputy->associateWithCourtOrder($courtOrder);

        $this->entityManager->persist($deputy);
        $this->entityManager->flush();

        $selectedCandidates = $this->sut->select()->candidates;

        static::assertEquals('UPDATE DEPUTY STATUS ON ORDER', $selectedCandidates[0]->action);
        static::assertFalse($selectedCandidates[0]->deputyStatusOnOrder);
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

        $selectedCandidates = $this->sut->select()->candidates;

        static::assertEquals('INSERT ORDER DEPUTY', $selectedCandidates[0]->action);
        static::assertEquals($courtOrder->getCourtOrderUid(), $selectedCandidates[0]->orderUid);
        static::assertEquals($deputy->getDeputyUid(), $selectedCandidates[0]->deputyUid);
    }

    public function testAddingNewSingleCourtOrder(): void
    {
        $stagingDeputyshipObject = $this->entityManager->getRepository(StagingDeputyship::class)->findOneBy(['orderUid' => '700000001104', 'deputyUid' => '700761111004']);

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

        $selectedCandidates = $this->sut->select()->candidates;

        foreach ($selectedCandidates as $candidate) {
            if ('INSERT ORDER' === $candidate->action) {
                static::assertEquals($stagingDeputyshipObject->orderUid, $candidate->orderUid);
                static::assertEquals($stagingDeputyshipObject->deputyUid, $candidate->deputyUid);
                static::assertEquals($client->getId(), $candidate->clientId);
            } elseif ('INSERT ORDER DEPUTY' === $candidate->action) {
                static::assertEquals($stagingDeputyshipObject->orderUid, $candidate->orderUid);
                static::assertEquals($stagingDeputyshipObject->deputyUid, $candidate->deputyUid);
                static::assertEquals($stagingDeputyshipObject->deputyStatusOnOrder, $candidate->deputyStatusOnOrder);
                static::assertEquals($deputy->getId(), $candidate->deputyId);
            }
        }
    }
}
