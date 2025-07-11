<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Client;
use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\SynchronisableInterface;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\ParameterBag;

class ReportRepositoryTest extends ApiBaseTestCase
{
    private ReportRepository $sut;
    private array $queryResult;
    private Checklist|array $queuedChecklists = [];
    public const QUERY_LIMIT = 2;

    public function setUp(): void
    {
        parent::setUp();
        $this->purgeDatabase();

        $this->fixtures = new Fixtures($this->entityManager);
        $this->sut = $this->entityManager->getRepository(Report::class);
    }

    /**
     * @test
     */
    public function fetchQueuedChecklists()
    {
        $this
            ->ensureChecklistsExistInDatabase()
            ->fetchChecklists()
            ->assertOnlyAlimitedNumberOfQueuedChecklistsAreReturned()
            ->assertQueuedChecklistsAreUpdatedToInProgress();
    }

    /** @test */
    public function findAllActiveReportsByCaseNumbersAndRoleIsCaseInsensitive()
    {
        $client = (new Client())->setCaseNumber('4932965t');
        $this->entityManager->persist($client);

        $existingReport = $this->buildReport($client);

        $this->entityManager->flush();
        $this->entityManager->refresh($existingReport);
        $this->entityManager->refresh($client);
        $this->entityManager->refresh($client->getUsers()[0]);

        $result = $this->sut->findAllActiveReportsByCaseNumbersAndRole(['4932965T'], $client->getUsers()[0]->getRoleName());
        self::assertEquals($existingReport, $result[0]);
    }

    /**
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function ensureChecklistsExistInDatabase(): ReportRepositoryTest
    {
        $client = (new Client())->setCaseNumber('49329657');
        $this->entityManager->persist($client);

        $this->queuedChecklists[] = $this->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_QUEUED);
        $this->queuedChecklists[] = $this->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_QUEUED);
        $this->queuedChecklists[] = $this->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_QUEUED);
        $this->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_SUCCESS);
        $this->buildChecklistWithStatus($client, null);

        $this->entityManager->flush();

        return $this;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function fetchChecklists(): ReportRepositoryTest
    {
        $this->queryResult = $this->sut->getReportsIdsWithQueuedChecklistsAndSetChecklistsToInProgress(self::QUERY_LIMIT);

        // Add 0.5 second buffer time to all doctrine updates and stop test from intermittently failing
        usleep(500000);

        return $this;
    }

    private function assertOnlyAlimitedNumberOfQueuedChecklistsAreReturned(): ReportRepositoryTest
    {
        $this->assertCount(self::QUERY_LIMIT, $this->queryResult);

        return $this;
    }

    private function assertQueuedChecklistsAreUpdatedToInProgress(): void
    {
        $repository = $this->entityManager->getRepository(Checklist::class);
        $result = $repository->findBy(['synchronisationStatus' => SynchronisableInterface::SYNC_STATUS_IN_PROGRESS]);
        $this->assertCount(2, $result);
        $this->assertEquals($this->queuedChecklists[0]->getId(), $result[0]->getId());
        $this->assertEquals($this->queuedChecklists[1]->getId(), $result[1]->getId());
    }

    /**
     * @return ReportRepositoryTest
     *
     * @throws ORMException
     */
    private function buildChecklistWithStatus(Client $client, ?string $status): Checklist
    {
        $report = $this->buildReport($client);
        $checklist = new Checklist($report);

        if ($status) {
            $checklist->setSynchronisationStatus($status);
        }

        $this->entityManager->persist($checklist);

        return $checklist;
    }

    /**
     * @throws \Exception
     */
    private function buildReport(Client $client): Report
    {
        $startDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $endDate = $startDate->add(new \DateInterval('P1D'));
        $report = new Report($client, Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS, $startDate, $endDate);

        $user = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setEmail(sprintf('email%s@test.com', rand(1, 100000)))
            ->setPassword('password')
            ->setRoleName('ROLE_LAY');

        $client->addUser($user);

        $reportSubmission = new ReportSubmission($report, $user);
        $report->addReportSubmission($reportSubmission);

        $this->entityManager->persist($report);
        $this->entityManager->persist($reportSubmission);
        $this->entityManager->persist($user);
        $this->entityManager->persist($client);

        return $report;
    }

    /**
     * @throws ORMException
     */
    public function testReportsAreSortedByEndDateAndGroupedForDualReports(): void
    {
        // create organisation
        $org = $this->fixtures->createOrganisations(1);

        // create clients and add to org
        $user = $this->fixtures->createUser()->setRoleName(User::ROLE_PROF);

        $client1 = $this->fixtures->createClient($user);
        $client2 = $this->fixtures->createClient($user);
        $clientDual = $this->fixtures->createClient($user);

        $this->entityManager->flush();

        $this->fixtures->addClientToOrganisation($client1->getId(), $org[0]->getId());
        $this->fixtures->addClientToOrganisation($client2->getId(), $org[0]->getId());
        $this->fixtures->addClientToOrganisation($clientDual->getId(), $org[0]->getId());

        // create reports for clients
        $report1 = $this->fixtures->createReport($client1)->setDueDate(new \DateTime('2025-08-01'))->setEndDate(new \DateTime('2025-07-10'));
        $report2 = $this->fixtures->createReport($client2)->setDueDate(new \DateTime('2025-03-01'))->setEndDate(new \DateTime('2025-02-10'));

        $dualReport1 = $this->fixtures->createReport($clientDual)->setDueDate(new \DateTime('2025-02-01'))->setEndDate(new \DateTime('2025-01-10'));
        $dualReport2 = $this->fixtures->createReport($clientDual)->setDueDate(new \DateTime('2025-06-01'))->setEndDate(new \DateTime('2025-05-10'));

        $this->entityManager->flush();

        $reports = $this->sut->getAllByDeterminant([$org[0]->getId()], 2, new ParameterBag(), 'reports', 'notStarted');

        self::assertCount(4, $reports);
        self::assertEquals($reports[0]['id'], $dualReport1->getId());
        self::assertEquals($reports[1]['id'], $dualReport2->getId());
        self::assertEquals($reports[2]['id'], $report2->getId());
        self::assertEquals($reports[3]['id'], $report1->getId());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
