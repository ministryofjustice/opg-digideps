<?php

namespace App\Tests\Integration\Entity;

use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Repository\DeputyRepository;
use App\TestHelpers\DeputyTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Generator $faker;
    private DeputyRepository $deputyRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $this->faker = Factory::create();

        /* @var DeputyRepository $deputyRepository */
        $this->deputyRepository = $this->em->getRepository(Deputy::class);
    }

    protected function tearDown(): void
    {
        (new ORMPurger($this->em))->purge();
    }

    public function testGetCourtOrdersWithStatus(): void
    {
        $fakeUid = $this->faker->unique()->randomNumber(8);

        $deputyHelper = new DeputyTestHelper();
        $deputy = $deputyHelper->generateDeputy();
        $deputy->setLastname('MONK');

        $courtOrder = new CourtOrder();
        $courtOrder
            ->setCourtOrderUid($fakeUid)
            ->setOrderType('hybrid')
            ->setStatus('ACTIVE')
            ->setOrderMadeDate(new \DateTime('2020-06-14'));

        $deputy->associateWithCourtOrder($courtOrder);

        $this->em->persist($courtOrder);
        $this->em->persist($deputy);
        $this->em->flush();

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
        $fakeCourtOrderUid = $this->faker->unique()->randomNumber(8);
        $fakeDeputyUid = $this->faker->unique()->randomNumber(8);

        $deputyHelper = new DeputyTestHelper();
        $deputy = $deputyHelper->generateDeputy(deputyUid: $fakeDeputyUid);
        $deputy->setLastname('VOLO');

        $courtOrder = new CourtOrder();
        $courtOrder
            ->setCourtOrderUid($fakeCourtOrderUid)
            ->setOrderType('hybrid')
            ->setStatus('ACTIVE')
            ->setOrderMadeDate(new \DateTime('2020-06-14'));

        $deputy->associateWithCourtOrder($courtOrder);

        $this->em->persist($courtOrder);
        $this->em->persist($deputy);
        $this->em->flush();

        // check deputy persisted and court_order <-> deputy association exists
        $retrievedDeputy = $this->deputyRepository->findOneBy(['deputyUid' => $fakeDeputyUid]);
        self::assertNotNull($retrievedDeputy);

        $courtOrders = $retrievedDeputy->getCourtOrdersWithStatus();
        self::assertCount(1, $courtOrders);

        // delete the deputy and confirm that it and the court_order_deputy entry are gone
        $this->em->remove($deputy);
        $this->em->flush();
    }
}
