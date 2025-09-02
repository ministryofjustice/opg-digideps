<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity;

use DateTime;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Repository\DeputyRepository;
use App\TestHelpers\DeputyTestHelper;
use App\Tests\Integration\ApiBaseTestCase;
use Faker\Factory;
use Faker\Generator;

final class DeputyTest extends ApiBaseTestCase
{
    private Generator $faker;
    private DeputyRepository $deputyRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /* @var DeputyRepository $deputyRepository */
        $deputyRepository = $this->entityManager->getRepository(Deputy::class);

        $this->deputyRepository = $deputyRepository;
    }

    public function testGetCourtOrdersWithStatus(): void
    {
        $fakeUid = strval($this->faker->unique()->randomNumber(8));

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

        $this->entityManager->persist($courtOrder);
        $this->entityManager->persist($deputy);
        $this->entityManager->flush();

        // retrieve the deputy from the db and check the association is populated correctly
        $retrievedDeputy = $this->deputyRepository->findOneBy(['deputyUid' => $deputy->getDeputyUid()]);

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
        $fakeCourtOrderUid = strval($this->faker->unique()->randomNumber(8));
        $fakeDeputyUid = strval($this->faker->unique()->randomNumber(8));

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

        $this->entityManager->persist($courtOrder);
        $this->entityManager->persist($deputy);
        $this->entityManager->flush();

        // check deputy persisted and court_order <-> deputy association exists
        $retrievedDeputy = $this->deputyRepository->findOneBy(['deputyUid' => $fakeDeputyUid]);
        self::assertNotNull($retrievedDeputy);

        $courtOrders = $retrievedDeputy->getCourtOrdersWithStatus();
        self::assertCount(1, $courtOrders);

        // delete the deputy and confirm that it and the court_order_deputy entry are gone
        $this->entityManager->remove($deputy);
        $this->entityManager->flush();
    }
}
