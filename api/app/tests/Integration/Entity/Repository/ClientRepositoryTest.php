<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\ApiBaseTestCase;

class ClientRepositoryTest extends ApiBaseTestCase
{
    private ClientRepository $sut;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ClientRepository $sut */
        $sut = $this->entityManager->getRepository(Client::class);

        $this->sut = $sut;

        $this->purgeDatabase();
    }

    public function testgetAllClientsAndReportsByDeputyUid()
    {
        $userHelper = new UserTestHelper();
        $clientHelper = new ClientTestHelper();

        $clientOne = $clientHelper->generateClient($this->entityManager);
        $activeUserOne = $userHelper->createAndPersistUser($this->entityManager, $clientOne);

        $clientTwo = $clientHelper->generateClient($this->entityManager);
        $activeUserTwo = $userHelper->createAndPersistUser($this->entityManager, $clientTwo);

        $clientThree = $clientHelper->generateClient($this->entityManager, $activeUserTwo);

        $activeUserOne->setDeputyUid(12345678);
        $activeUserTwo->setDeputyUid($activeUserOne->getDeputyUid());

        $this->entityManager->persist($clientThree);
        $this->entityManager->flush();

        $clients = $this->sut->getAllClientsAndReportsByDeputyUid($activeUserOne->getDeputyUid());

        self::assertCount(3, $clients);
        self::assertContains($clientOne, $clients);
        self::assertContains($clientTwo, $clients);
        self::assertContains($clientThree, $clients);
    }
}
