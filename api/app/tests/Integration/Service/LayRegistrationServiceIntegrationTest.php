<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Repository\ClientRepository;
use App\Service\LayRegistrationService;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;

class LayRegistrationServiceIntegrationTest extends ApiBaseTestCase
{
    private Fixtures $fixtures;
    private ClientRepository $clientRepo;
    private LayRegistrationService $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures($this->entityManager);

        /** @var ClientRepository $clientRepo */
        $clientRepo = $this->entityManager->getRepository(Client::class);
        $this->clientRepo = $clientRepo;

        /** @var LayRegistrationService $sut */
        $sut = $this->container->get(LayRegistrationService::class);
        $this->sut = $sut;
    }

    private function addClient(string $caseNumber): Client
    {
        return $this->fixtures->createClient(null, ['setCaseNumber' => $caseNumber]);
    }

    public function testAddMissingReportsHybrid(): void
    {
        $caseNumber = '16368651';

        // pre-reg entries for hybrid report
        $this->fixtures->createPreRegistration($caseNumber, 'OPG102', 'hw');
        $this->fixtures->createPreRegistration($caseNumber, 'OPG102', 'pfa');

        // client relating to those entries
        $client = $this->addClient($caseNumber);

        $this->entityManager->flush();

        // test
        $reportsAdded = $this->sut->addMissingReports();

        self::assertEquals(1, $reportsAdded);

        // check the reports are correct
        $reports = $this->clientRepo->find($client->getId())->getReports();

        self::assertCount(1, $reports);
        self::assertEquals(Report::LAY_COMBINED_HIGH_ASSETS_TYPE, $reports[0]->getType());
        self::assertTrue($reports[0]->isHybrid());
    }
}
