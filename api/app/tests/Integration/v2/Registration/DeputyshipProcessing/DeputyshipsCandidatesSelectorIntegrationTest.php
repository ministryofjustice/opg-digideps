<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Tests\Integration\ApiTestTrait;
use DateTime;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\StagingDeputyship;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyshipsCandidatesSelectorIntegrationTest extends KernelTestCase
{
    use ApiTestTrait;

    private DeputyshipsCandidatesSelector $sut;

    /*
     * For some reason, this test class does not behave when the container created by ApiBaseTestCase::setUpBeforeClass
     * is used to fetch the objects used by the test. That's why this property is overridden in this method.
     *
     * To see why, try commenting out the line which sets self::$container inside setUp()
     * and run the integration tests, which will fail.
     *
     * I think it's likely to do with having two different entity managers, one for reads and the other for writes,
     * though I've been unable to confirm this as the entity managers used by the SUT and those fetched for the
     * test appear to be equal.
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::configureTest();

        self::purgeDatabase();

        $fileLocation = dirname(__FILE__) . '/../../../../csv/deputyshipsReport2.csv';

        $csvLoader = self::$container->get(DeputyshipsCSVLoader::class);
        $csvLoader->load($fileLocation);

        /** @var ?DeputyshipsCandidatesSelector $sut */
        $sut = self::$container->get(DeputyshipsCandidatesSelector::class);
        $this->sut = $sut;
    }

    public function testCourtOrderStatusChange(): void
    {
        $courtOrder = new CourtOrder();
        $courtOrderUid = '700000001101';

        $courtOrder->setCourtOrderUid($courtOrderUid);
        $courtOrder->setOrderType('pfa');
        $courtOrder->setStatus('OPEN');
        $courtOrder->setOrderMadeDate(new DateTime('2018-01-21'));

        self::$entityManager->persist($courtOrder);
        self::$entityManager->flush();

        $selectedCandidates = iterator_to_array($this->sut->select()->candidates);

        static::assertEquals(DeputyshipCandidateAction::UpdateOrderStatus, $selectedCandidates[0]['action']);
        static::assertEquals('ACTIVE', $selectedCandidates[0]['status']);
    }

    public function testDeputyStatusChangeOnCourtOrder(): void
    {
        $courtOrder = new CourtOrder();
        $courtOrderUid = '700000001102';
        $courtOrder->setCourtOrderUid($courtOrderUid);
        $courtOrder->setOrderType('hw');
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new DateTime('2019-01-21'));

        self::$entityManager->persist($courtOrder);

        $deputy = new Deputy();
        $deputy->setFirstname('John');
        $deputy->setLastname('Snow');
        $deputy->setEmail1('john.snow@test.co.uk');
        $deputy->setDeputyUid('700761111002');

        self::$entityManager->persist($deputy);

        $deputy->associateWithCourtOrder($courtOrder);

        self::$entityManager->persist($deputy);
        self::$entityManager->flush();

        $selectedCandidates = iterator_to_array($this->sut->select()->candidates);

        static::assertEquals(DeputyshipCandidateAction::UpdateDeputyStatus, $selectedCandidates[0]['action']);
        static::assertFalse($selectedCandidates[0]['deputyStatusOnOrder']);
    }

    public function testNewDeputyAddedToCourtOrder(): void
    {
        $courtOrder = new CourtOrder();
        $courtOrderUid = '700000001103';

        $courtOrder->setCourtOrderUid($courtOrderUid);
        $courtOrder->setOrderType('hw');
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new DateTime('2019-01-21'));
        self::$entityManager->persist($courtOrder);

        $deputy = new Deputy();
        $deputy->setFirstname('John');
        $deputy->setLastname('Snow');
        $deputy->setEmail1('john.snow@test.co.uk');
        $deputy->setDeputyUid('700761111003');
        self::$entityManager->persist($deputy);

        self::$entityManager->flush();

        $selectedCandidates = iterator_to_array($this->sut->select()->candidates);

        static::assertEquals(DeputyshipCandidateAction::InsertOrderDeputy, $selectedCandidates[0]['action']);
        static::assertEquals($courtOrder->getCourtOrderUid(), $selectedCandidates[0]['orderUid']);
        static::assertEquals($deputy->getDeputyUid(), $selectedCandidates[0]['deputyUid']);
    }

    public function testAddingNewSingleCourtOrder(): void
    {
        $stagingDeputyshipObject = self::$entityManager->getRepository(StagingDeputyship::class)->findOneBy(['orderUid' => '700000001104', 'deputyUid' => '700761111004']);

        $deputy = new Deputy();
        $deputy->setFirstname('Stuart');
        $deputy->setLastname('One');
        $deputy->setEmail1('stuart.one@test.co.uk');
        $deputy->setDeputyUid('700761111004');
        self::$entityManager->persist($deputy);

        $client = ClientTestHelper::create()->generateClient(self::$entityManager, null, null, '61111002');
        $report = ReportTestHelper::create()->generateReport(self::$entityManager, $client, '104', new DateTime('2019-01-21'), new DateTime('2020-01-21'));

        $client->addReport($report);
        $report->setClient($client);

        self::$entityManager->persist($client);
        self::$entityManager->persist($report);
        self::$entityManager->flush();

        $selectedCandidates = iterator_to_array($this->sut->select()->candidates);

        foreach ($selectedCandidates as $candidate) {
            if (DeputyshipCandidateAction::InsertOrder === $candidate['action']) {
                static::assertEquals($stagingDeputyshipObject->orderUid, $candidate['orderUid']);
                static::assertEquals($client->getId(), $candidate['clientId']);
            } elseif (DeputyshipCandidateAction::InsertOrderDeputy === $candidate['action']) {
                static::assertEquals($stagingDeputyshipObject->orderUid, $candidate['orderUid']);
                static::assertEquals($stagingDeputyshipObject->deputyUid, $candidate['deputyUid']);
                static::assertEquals($stagingDeputyshipObject->deputyStatusOnOrder, $candidate['deputyStatusOnOrder']);
                static::assertEquals($deputy->getId(), $candidate['deputyId']);
            }
        }
    }
}
