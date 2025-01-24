<?php

namespace App\Tests\Unit\Entity;

use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\TestHelpers\DeputyTestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testGetCourtOrdersWithStatus()
    {
        $faker = Factory::create();

        $deputyHelper = new DeputyTestHelper();
        $deputy = $deputyHelper->generateDeputy();

        $courtOrder = new CourtOrder();
        $courtOrder
            ->setCourtOrderUid($faker->unique()->randomNumber(8))
            ->setType('hybrid')
            ->setActive(true);

        $courtOrderDeputy = new CourtOrderDeputy();

        $courtOrderDeputy
            ->setDeputy($deputy)
            ->setCourtOrder($courtOrder)
            ->setDischarged(false);

        $this->em->persist($deputy);

        $this->em->persist($courtOrder);

        $this->em->persist($courtOrderDeputy);
        $this->em->flush();

        // one deputy can have many court orders
        $actual = $deputy->getCourtOrdersWithStatus();

        $expected = ['courtOrder' => $courtOrder, 'discharged' => false];

        $this->assertEquals($expected, $actual);
    }
}
