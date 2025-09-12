<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Repository\ClientRepository;
use App\Service\LayRegistrationService;
use App\Tests\Integration\ApiTestCase;
use App\Tests\Integration\Fixtures;

class LayRegistrationServiceIntegrationTest extends ApiTestCase
{
    private static Fixtures $fixtures;
    private static ClientRepository $clientRepo;
    private static LayRegistrationService $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var ClientRepository $clientRepo */
        $clientRepo = self::$entityManager->getRepository(Client::class);
        self::$clientRepo = $clientRepo;

        /** @var LayRegistrationService $sut */
        $sut = self::$container->get(LayRegistrationService::class);
        self::$sut = $sut;
    }

    private function addClient(string $caseNumber): Client
    {
        return self::$fixtures->createClient(null, ['setCaseNumber' => $caseNumber]);
    }

    public function testAddMissingReportsHybrid(): void
    {
        $caseNumber = '16368651';

        // pre-reg entries for hybrid report
        $preReg1 = self::$fixtures->createPreRegistration($caseNumber, 'OPG102', 'hw');
        self::$entityManager->persist($preReg1);

        $preReg2 = self::$fixtures->createPreRegistration($caseNumber, 'OPG102', 'pfa');
        self::$entityManager->persist($preReg2);

        // client relating to those entries
        $client = $this->addClient($caseNumber);

        self::$entityManager->flush();

        // test
        $reportsAdded = self::$sut->addMissingReports();

        self::assertEquals(1, $reportsAdded);

        // check the reports are correct
        $reports = self::$clientRepo->find($client->getId())->getReports();

        self::assertCount(1, $reports);
        self::assertEquals(Report::LAY_COMBINED_HIGH_ASSETS_TYPE, $reports[0]->getType());
        self::assertTrue($reports[0]->isHybrid());
    }

    // this is to test that batching of persists and flushes works correctly
    public function testAddMissingReportsMultipleClients(): void
    {
        $clients = [];
        for ($i = 0; $i < 10; ++$i) {
            $caseNumber = "9933442$i";
            $clients[] = $this->addClient($caseNumber);
            self::$entityManager->persist(self::$fixtures->createPreRegistration($caseNumber, 'OPG104', 'hw'));
        }

        self::$entityManager->flush();

        $reportsAdded = self::$sut->addMissingReports(batchSize: 3);

        self::assertEquals(10, $reportsAdded);

        foreach ($clients as $client) {
            $reports = self::$clientRepo->find($client->getId())->getReports();
            self::assertCount(1, $reports);
        }
    }
}
