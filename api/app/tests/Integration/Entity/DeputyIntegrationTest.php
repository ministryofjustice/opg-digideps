<?php

namespace App\Tests\Integration\Entity;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Domain\CourtOrder\CourtOrderReportType;
use App\Domain\CourtOrder\CourtOrderType;
use App\Tests\Integration\ApiIntegrationTestCase;
use DateTime;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Repository\DeputyRepository;
use App\TestHelpers\DeputyTestHelper;
use Faker\Factory;
use Faker\Generator;

class DeputyIntegrationTest extends ApiIntegrationTestCase
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
            ->setOrderType(CourtOrderType::PFA)
            ->setOrderKind(CourtOrderKind::Hybrid)
            ->setOrderReportType(CourtOrderReportType::OPG102)
            ->setStatus('ACTIVE')
            ->setOrderMadeDate(new DateTime('2020-06-14'));

        $deputy->associateWithCourtOrder($courtOrder);

        self::$entityManager->persist($courtOrder);
        self::$entityManager->persist($deputy);
        self::$entityManager->flush();

        // retrieve the deputy from the db and check the association is populated correctly
        $retrievedDeputy = self::$deputyRepository->findOneBy(['deputyUid' => $deputy->getDeputyUid()]);

        $actual = $retrievedDeputy->getCourtOrdersWithStatus();
        $this->assertArrayHasKey('courtOrder', $actual[0]);
        $this->assertArrayHasKey('isActive', $actual[0]);

        $isActive = $actual[0]['isActive'];
        $actualCourtOrder = $actual[0]['courtOrder'];

        $this->assertCount(1, $actual);
        $this->assertTrue($isActive);
        $this->assertEquals($fakeUid, $actualCourtOrder->getCourtOrderUid());
        $this->assertEquals(CourtOrderKind::Hybrid, $actualCourtOrder->getOrderKind());
        self::assertEquals(CourtOrderType::PFA, $actualCourtOrder->getOrderType());
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
            ->setOrderType(CourtOrderType::PFA)
            ->setOrderKind(CourtOrderKind::Hybrid)
            ->setOrderReportType(CourtOrderReportType::OPG102)
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
