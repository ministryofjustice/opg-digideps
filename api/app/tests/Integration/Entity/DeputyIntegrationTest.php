<?php

namespace app\tests\Integration\Entity;

use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\TestHelpers\DeputyTestHelper;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyIntegrationTest extends KernelTestCase
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
        $fakeUid = $faker->unique()->randomNumber(8);

        $deputyHelper = new DeputyTestHelper();
        $deputy = $deputyHelper->generateDeputy();
        $deputy->setLastname('MONK');

        $courtOrder = new CourtOrder();
        $courtOrder
            ->setCourtOrderUid($fakeUid)
            ->setType('hybrid')
            ->setActive(true);

        $deputy->associateWithCourtOrder($courtOrder);

        $this->em->persist($courtOrder);
        $this->em->persist($deputy);
        $this->em->flush();

        // retrieve the deputy from the db and check the association is populated correctly
        $repo = $this->em->getRepository(Deputy::class);
        $retrievedDeputy = $repo->findOneBy(['deputyUid' => $deputy->getDeputyUid()]);

        $actual = $retrievedDeputy->getCourtOrdersWithStatus();
        $actualDischarged = $actual[0]['discharged'];
        $actualCourtOrder = $actual[0]['courtOrder'];

        $this->assertEquals(1, count($actual));
        $this->assertEquals(false, $actualDischarged);
        $this->assertEquals($fakeUid, $actualCourtOrder->getCourtOrderUid());
        $this->assertEquals('hybrid', $actualCourtOrder->getType());
    }
}
