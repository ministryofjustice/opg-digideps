<?php

declare(strict_types=1);

namespace app\tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Entity\Deputy;
use App\Entity\Ndr\Ndr;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesGroup;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use App\v2\Service\DeputyshipCandidatesConverter;
use Doctrine\ORM\Query\Expr\Join;

class DeputyshipCandidateConverterIntegrationIntegrationTest extends ApiIntegrationTestCase
{
    private static DeputyshipCandidatesConverter $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var DeputyshipCandidatesConverter $sut */
        $sut = self::$container->get(DeputyshipCandidatesConverter::class);
        self::$sut = $sut;
    }

    private function hasCourtOrderDeputyAssociationBeenAdded(Deputy $deputy, string $orderUid): bool
    {
        // check that the court order <-> deputy association has been added
        // (note this will not exist unless the court order exists as court_order_id is a foreign key)
        /** @var int $result */
        $result = self::$entityManager->createQueryBuilder()
            ->select('COUNT(1)')
            ->from(CourtOrderDeputy::class, 'cod')
            ->innerJoin(CourtOrder::class, 'co', Join::WITH, 'co = cod.courtOrder')
            ->where('cod.deputy = :deputy')
            ->andWhere('co.courtOrderUid = :courtOrderUid')
            ->setParameters(['deputy' => $deputy, 'courtOrderUid' => $orderUid])
            ->getQuery()
            ->getSingleScalarResult();

        return $result === 1;
    }

    // test the case where an order is inserted and its ID immediately looked up afterwards to insert a
    // court_order_deputy association (we seem to have some errors around this area, possibly)
    public function testConvertInsertedOrderCanBeLookedUpImmediately(): void
    {
        $orderUid = '42526746';
        $caseNumber = '52363467';

        $client = new Client();
        $client->setCaseNumber($caseNumber);
        self::$entityManager->persist($client);

        $deputy = new Deputy();
        $deputy->setFirstname('Alf');
        $deputy->setLastname('Alf');
        $deputy->setEmail1('alf@notarealemail.com');
        $deputy->setDeputyUid('14235674');
        self::$entityManager->persist($deputy);

        self::$entityManager->flush();

        $candidatesGroup = new DeputyshipCandidatesGroup();
        $candidatesGroup->orderUid = $orderUid;
        $candidatesGroup->insertOrder = [
            'action' => DeputyshipCandidateAction::InsertOrder,
            'orderUid' => $orderUid,
            'orderType' => 'pfa',
            'status' => 'ACTIVE',
            'orderMadeDate' => '2025-06-10',
            'clientId' => $client->getId(),
        ];
        $candidatesGroup->insertOthers = [
            [
                'action' => DeputyshipCandidateAction::InsertOrderDeputy,
                'orderUid' => $orderUid,
                'deputyId' => $deputy->getId(),
                'deputyStatusOnOrder' => false,
            ],
        ];

        $result = self::$sut->convert($candidatesGroup, dryRun: false);

        self::assertCount(0, $result->getErrors(), 'candidate group should have no errors');
        self::assertStringContainsString('failed candidates = 0', $result->getMessage());
        self::assertStringContainsString('applied candidates = 2', $result->getMessage());
        self::assertTrue($this->hasCourtOrderDeputyAssociationBeenAdded($deputy, $orderUid));
    }

    // test the situation where a court order already exists and we are associating a deputy with it
    public function testConvertInsertOrderDeputyWhenCourtOrderExists(): void
    {
        $orderUid = '14255666';
        $caseNumber = '51223467';

        $client = new Client();
        $client->setCaseNumber($caseNumber);
        self::$entityManager->persist($client);

        $courtOrder = new CourtOrder();
        $courtOrder->setCourtOrderUid($orderUid);
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setClient($client);
        $courtOrder->setOrderType('pfa');
        $courtOrder->setOrderMadeDate(new \DateTime());
        self::$entityManager->persist($courtOrder);

        $deputy = new Deputy();
        $deputy->setFirstname('Vev');
        $deputy->setLastname('Alfome');
        $deputy->setEmail1('vev@notarealemail.com');
        $deputy->setDeputyUid('14235678');
        self::$entityManager->persist($deputy);

        self::$entityManager->flush();

        $candidatesGroup = new DeputyshipCandidatesGroup();
        $candidatesGroup->orderUid = $orderUid;
        $candidatesGroup->insertOrder = null;
        $candidatesGroup->insertOthers = [
            [
                'action' => DeputyshipCandidateAction::InsertOrderDeputy,
                'orderUid' => $orderUid,
                'deputyId' => $deputy->getId(),
                'deputyStatusOnOrder' => false,
            ],
        ];

        $result = self::$sut->convert($candidatesGroup, dryRun: false);

        self::assertCount(0, $result->getErrors(), 'candidate group should have no errors');
        self::assertStringContainsString('failed candidates = 0', $result->getMessage());
        self::assertStringContainsString('applied candidates = 1', $result->getMessage());
        self::assertTrue($this->hasCourtOrderDeputyAssociationBeenAdded($deputy, $orderUid));
    }
}
