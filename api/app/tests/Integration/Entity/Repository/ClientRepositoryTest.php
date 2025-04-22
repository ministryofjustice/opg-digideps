<?php

namespace App\Entity\Repository;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\UserTestHelper;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientRepositoryTest extends WebTestCase
{
    private ClientRepository $sut;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();

        $this->sut = $this->em->getRepository(Client::class);

        $purger = new ORMPurger($this->em);
        $purger->purge();
    }

    public function testgetAllClientsAndReportsByDeputyUid()
    {
        $userHelper = new UserTestHelper();
        $clientHelper = new ClientTestHelper();

        $clientOne = $clientHelper->generateClient($this->em);
        $activeUserOne = $userHelper->createAndPersistUser($this->em, $clientOne);

        $clientTwo = $clientHelper->generateClient($this->em);
        $activeUserTwo = $userHelper->createAndPersistUser($this->em, $clientTwo);

        $clientThree = $clientHelper->generateClient($this->em, $activeUserTwo);

        $activeUserOne->setDeputyUid('12345678');
        $activeUserTwo->setDeputyUid($activeUserOne->getDeputyUid());

        $this->em->persist($clientThree);
        $this->em->flush();

        $clients = $this->sut->getAllClientsAndReportsByDeputyUid($activeUserOne->getDeputyUid());

        self::assertCount(3, $clients);
        self::assertContains($clientOne, $clients);
        self::assertContains($clientTwo, $clients);
        self::assertContains($clientThree, $clients);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        unset($this->em);
    }
}
