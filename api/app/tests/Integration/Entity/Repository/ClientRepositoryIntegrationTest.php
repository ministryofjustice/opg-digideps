<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Entity\Repository;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Repository\ClientRepository;
use OPG\Digideps\Backend\TestHelpers\ClientTestHelper;
use OPG\Digideps\Backend\TestHelpers\UserTestHelper;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class ClientRepositoryIntegrationTest extends ApiIntegrationTestCase
{
    private static ClientRepository $sut;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ClientRepository $sut */
        $sut = self::$entityManager->getRepository(Client::class);

        self::$sut = $sut;

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

        $clients = self::$sut->getAllClientsAndReportsByDeputyUid($activeUserOne->getDeputyUid());

        self::assertCount(3, $clients);
        self::assertContains($clientOne, $clients);
        self::assertContains($clientTwo, $clients);
        self::assertContains($clientThree, $clients);
    }
}
