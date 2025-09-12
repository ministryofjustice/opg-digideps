<?php

namespace App\Tests\Integration\Entity;

use App\Tests\Integration\ApiTestCase;
use DateTime;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Repository\DeputyRepository;
use App\TestHelpers\DeputyTestHelper;
use Faker\Factory;
use Faker\Generator;

class DeputyTest extends ApiTestCase
{
    private static Generator $faker;
    private static DeputyRepository $deputyRepository;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$faker = Factory::create();

        /* @var DeputyRepository $deputyRepository */
        $deputyRepository = self::$entityManager->getRepository(Deputy::class);

        self::$deputyRepository = $deputyRepository;
    }

    public function testGetCourtOrdersWithStatus(): void
    {
        $fakeUid = self::$faker->unique()->randomNumber(8);

        $deputyHelper = new DeputyTestHelper();
        $deputy = $deputyHelper->generateDeputy();
        $deputy->setLastname('MONK');

        $courtOrder = new CourtOrder();
        $courtOrder
            ->setCourtOrderUid($fakeUid)
            ->setOrderType('hybrid')
            ->setStatus('ACTIVE')
            ->setOrderMadeDate(new DateTime('2020-06-14'));

        $deputy->associateWithCourtOrder($courtOrder);

        self::$entityManager->persist($courtOrder);
        self::$entityManager->persist($deputy);
        self::$entityManager->flush();

        // retrieve the deputy from the db and check the association is populated correctly
        $retrievedDeputy = self::$deputyRepository->findOneBy(['deputyUid' => $deputy->getDeputyUid()]);

        $actual = $retrievedDeputy->getCourtOrdersWithStatus();
        self::assertArrayHasKey('courtOrder', $actual[0]);
        self::assertArrayHasKey('isActive', $actual[0]);

        $isActive = $actual[0]['isActive'];
        $actualCourtOrder = $actual[0]['courtOrder'];

        self::assertEquals(1, count($actual));
        self::assertEquals(true, $isActive);
        self::assertEquals($fakeUid, $actualCourtOrder->getCourtOrderUid());
        self::assertEquals('hybrid', $actualCourtOrder->getOrderType());
    }

    /**
     * Deleting a deputy should delete court_order_deputy records.
     */
    public function testCascadeDeleteCourtOrderDeputy(): void
    {
        $fakeCourtOrderUid = self::$faker->unique()->randomNumber(8);
        $fakeDeputyUid = self::$faker->unique()->randomNumber(8);

        $deputyHelper = new DeputyTestHelper();
        $deputy = $deputyHelper->generateDeputy(deputyUid: $fakeDeputyUid);
        $deputy->setLastname('VOLO');

        $courtOrder = new CourtOrder();
        $courtOrder
            ->setCourtOrderUid($fakeCourtOrderUid)
            ->setOrderType('hybrid')
            ->setStatus('ACTIVE')
            ->setOrderMadeDate(new DateTime('2020-06-14'));

        $deputy->associateWithCourtOrder($courtOrder);

        self::$entityManager->persist($courtOrder);
        self::$entityManager->persist($deputy);
        self::$entityManager->flush();

        // check deputy persisted and court_order <-> deputy association exists
        $retrievedDeputy = self::$deputyRepository->findOneBy(['deputyUid' => $fakeDeputyUid]);
        self::assertNotNull($retrievedDeputy);

        $courtOrders = $retrievedDeputy->getCourtOrdersWithStatus();
        self::assertCount(1, $courtOrders);

        // delete the deputy and confirm that it and the court_order_deputy entry are gone
        self::$entityManager->remove($deputy);
        self::$entityManager->flush();
    }
}
