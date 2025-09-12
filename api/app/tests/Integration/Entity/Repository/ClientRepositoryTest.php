<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\ApiTestCase;

class ClientRepositoryTest extends ApiTestCase
{
    private ClientRepository $sut;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ClientRepository $sut */
        $sut = self::$entityManager->getRepository(Client::class);

        $this->sut = $sut;

        $this->purgeDatabase();
    }

    public function testgetAllClientsAndReportsByDeputyUid()
    {
        $userHelper = UserTestHelper::create();
        $clientHelper = ClientTestHelper::create();

        $clientOne = $clientHelper->generateClient(self::$entityManager);
        $activeUserOne = $userHelper->createAndPersistUser(self::$entityManager, $clientOne);

        $clientTwo = $clientHelper->generateClient(self::$entityManager);
        $activeUserTwo = $userHelper->createAndPersistUser(self::$entityManager, $clientTwo);

        $clientThree = $clientHelper->generateClient(self::$entityManager, $activeUserTwo);

        $activeUserOne->setDeputyUid(12345678);
        $activeUserTwo->setDeputyUid($activeUserOne->getDeputyUid());

        self::$entityManager->persist($clientThree);
        self::$entityManager->flush();

        $clients = $this->sut->getAllClientsAndReportsByDeputyUid($activeUserOne->getDeputyUid());

        self::assertCount(3, $clients);
        self::assertContains($clientOne, $clients);
        self::assertContains($clientTwo, $clients);
        self::assertContains($clientThree, $clients);
    }
}
