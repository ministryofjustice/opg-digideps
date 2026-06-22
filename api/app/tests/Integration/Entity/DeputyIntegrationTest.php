<?php

namespace Tests\OPG\Digideps\Backend\Integration\Entity;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Entity\Client;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Repository\DeputyRepository;
use OPG\Digideps\Backend\TestHelpers\DeputyTestHelper;
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
        $fakeUid = (string)self::$faker->unique()->randomNumber(8);

        $deputyHelper = new DeputyTestHelper();
        $deputy = $deputyHelper->generateDeputy();
        $deputy->setLastname('MONK');

        $client = new Client();

        $courtOrder = new CourtOrder(
            $fakeUid,
            CourtOrderType::PFA,
            CourtOrderReportType::OPG102,
            CourtOrderKind::Hybrid,
            new \DateTime('2020-06-14'),
            $client
        );

        $deputy->associateWithCourtOrder($courtOrder);

        self::$entityManager->persist($courtOrder);
        self::$entityManager->persist($client);
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
        $fakeCourtOrderUid = (string)self::$faker->unique()->randomNumber(8);
        $fakeDeputyUid = (string)self::$faker->unique()->randomNumber(8);

        $deputyHelper = new DeputyTestHelper();
        $deputy = $deputyHelper->generateDeputy(deputyUid: $fakeDeputyUid);
        $deputy->setLastname('VOLO');
        $client = new Client();

        $courtOrder = new CourtOrder(
            $fakeCourtOrderUid,
            CourtOrderType::PFA,
            CourtOrderReportType::OPG102,
            CourtOrderKind::Hybrid,
            new \DateTime('2020-06-14'),
            $client
        );

        $deputy->associateWithCourtOrder($courtOrder);

        self::$entityManager->persist($courtOrder);
        self::$entityManager->persist($client);
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
