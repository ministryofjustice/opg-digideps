<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\DeputyshipProcessing;

use DateTime;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\StagingDeputyship;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Integration\ApiBaseTestCase;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCandidatesSelector;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVLoader;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

class DeputyshipsCandidatesSelectorIntegrationTest extends ApiBaseTestCase
{
    private DeputyshipsCandidatesSelector $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $fileLocation = dirname(__FILE__).'/../../../../csv/deputyshipsReport2.csv';

        $csvLoader = $this->container->get(DeputyshipsCSVLoader::class);
        $csvLoader->load($fileLocation);

        /** @var ?DeputyshipsCandidatesSelector $sut */
        $sut = $this->container->get(DeputyshipsCandidatesSelector::class);
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

        $this->entityManager->persist($courtOrder);
        $this->entityManager->flush();

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
        $this->entityManager->persist($courtOrder);

        $deputy = new Deputy();
        $deputy->setFirstname('John');
        $deputy->setLastname('Snow');
        $deputy->setEmail1('john.snow@test.co.uk');
        $deputy->setDeputyUid('700761111003');
        $this->entityManager->persist($deputy);

        $this->entityManager->flush();

        $selectedCandidates = iterator_to_array($this->sut->select()->candidates);

        static::assertEquals(DeputyshipCandidateAction::InsertOrderDeputy, $selectedCandidates[0]['action']);
        static::assertEquals($courtOrder->getCourtOrderUid(), $selectedCandidates[0]['orderUid']);
        static::assertEquals($deputy->getDeputyUid(), $selectedCandidates[0]['deputyUid']);
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

        $client = ClientTestHelper::create()->generateClient($this->entityManager, null, null, '61111002');
        $report = ReportTestHelper::create()->generateReport($this->entityManager, $client, '104', new DateTime('2019-01-21'), new DateTime('2020-01-21'));

        $client->addReport($report);
        $report->setClient($client);

        $this->entityManager->persist($client);
        $this->entityManager->persist($report);
        $this->entityManager->flush();

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
