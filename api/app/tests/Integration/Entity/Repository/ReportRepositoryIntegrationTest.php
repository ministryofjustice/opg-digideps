<?php

namespace App\Tests\Integration\Repository;

use App\Tests\Integration\ApiIntegrationTestCase;
use DateTime;
use DateTimeZone;
use DateInterval;
use Exception;
use App\Entity\Client;
use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\SynchronisableInterface;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\Tests\Integration\Fixtures;
use Symfony\Component\HttpFoundation\ParameterBag;

class ReportRepositoryIntegrationTest extends ApiIntegrationTestCase
{
    private array $queryResult;
    private Checklist|array $queuedChecklists = [];
    public const QUERY_LIMIT = 2;
    private static Fixtures $fixtures;
    private static ReportRepository $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var ReportRepository $repo */
        $repo = self::$entityManager->getRepository(Report::class);

        self::$sut = $repo;
    }

    /**
     * @throws Exception
     */
    private function ensureChecklistsExistInDatabase(): ReportRepositoryIntegrationTest
    {
        $client = (new Client())->setCaseNumber('49329657');
        self::$entityManager->persist($client);

        $this->queuedChecklists[] = $this->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_QUEUED);
        $this->queuedChecklists[] = $this->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_QUEUED);
        $this->queuedChecklists[] = $this->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_QUEUED);
        $this->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_SUCCESS);
        $this->buildChecklistWithStatus($client, null);

        self::$entityManager->flush();

        return $this;
    }

    private function fetchChecklists(): ReportRepositoryIntegrationTest
    {
        $this->queryResult = self::$sut->getReportsIdsWithQueuedChecklistsAndSetChecklistsToInProgress(self::QUERY_LIMIT);

        return $this;
    }

    private function assertOnlyAlimitedNumberOfQueuedChecklistsAreReturned(): ReportRepositoryIntegrationTest
    {
        $this->assertCount(self::QUERY_LIMIT, $this->queryResult);

        return $this;
    }

    private function assertQueuedChecklistsAreUpdatedToInProgress(): void
    {
        $repository = self::$entityManager->getRepository(Checklist::class);
        $result = $repository->findBy(['synchronisationStatus' => SynchronisableInterface::SYNC_STATUS_IN_PROGRESS]);
        $this->assertCount(2, $result);
        $this->assertEquals($this->queuedChecklists[0]->getId(), $result[0]->getId());
        $this->assertEquals($this->queuedChecklists[1]->getId(), $result[1]->getId());
    }

    /**
     * @throws Exception
     */
    private function buildChecklistWithStatus(Client $client, ?string $status): Checklist
    {
        $report = $this->buildReport($client);
        $checklist = new Checklist($report);

        if ($status) {
            $checklist->setSynchronisationStatus($status);
        }

        self::$entityManager->persist($checklist);

        return $checklist;
    }

    /**
     * @throws Exception
     */
    private function buildReport(Client $client): Report
    {
        $startDate = new DateTime('now', new DateTimeZone('UTC'));
        $endDate = $startDate->add(new DateInterval('P1D'));
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

        self::$entityManager->persist($report);
        self::$entityManager->persist($reportSubmission);
        self::$entityManager->persist($user);
        self::$entityManager->persist($client);

        return $report;
    }

    public function testReportsAreSortedByDueDate(): void
    {
        // create organisation
        $org = self::$fixtures->createOrganisations(1);

        // create clients and add to org
        $user = self::$fixtures->createUser(roleName: User::ROLE_PROF);

        $client1 = self::$fixtures->createClient($user);
        $client2 = self::$fixtures->createClient($user);
        $clientDual = self::$fixtures->createClient($user);

        self::$entityManager->flush();

        self::$fixtures->addClientToOrganisation($client1->getId(), $org[0]->getId());
        self::$fixtures->addClientToOrganisation($client2->getId(), $org[0]->getId());
        self::$fixtures->addClientToOrganisation($clientDual->getId(), $org[0]->getId());

        // create reports for clients
        $report1 = self::$fixtures->createReport($client1)->setDueDate(new DateTime('2025-08-01'))->setEndDate(new DateTime('2025-07-10'));
        $report2 = self::$fixtures->createReport($client2)->setDueDate(new DateTime('2025-03-01'))->setEndDate(new DateTime('2025-02-10'));

        $dualReport1 = self::$fixtures->createReport($clientDual)->setDueDate(new DateTime('2025-02-01'))->setEndDate(new DateTime('2025-01-10'));
        $dualReport2 = self::$fixtures->createReport($clientDual)->setDueDate(new DateTime('2025-06-01'))->setEndDate(new DateTime('2025-05-10'));

        self::$entityManager->flush();

        $reports = self::$sut->getAllByDeterminant([$org[0]->getId()], 2, new ParameterBag(), 'reports', 'notStarted');

        self::assertCount(4, $reports);
        self::assertEquals($reports[0]['id'], $dualReport1->getId());
        self::assertEquals($reports[1]['id'], $report2->getId());
        self::assertEquals($reports[2]['id'], $dualReport2->getId());
        self::assertEquals($reports[3]['id'], $report1->getId());
    }

    public function testFetchQueuedChecklists(): void
    {
        $this
            ->ensureChecklistsExistInDatabase()
            ->fetchChecklists()
            ->assertOnlyAlimitedNumberOfQueuedChecklistsAreReturned()
            ->assertQueuedChecklistsAreUpdatedToInProgress();
    }

    public function testFindAllActiveReportsByCaseNumbersAndRoleIsCaseInsensitive(): void
    {
        $client = (new Client())->setCaseNumber('4932965t');
        self::$entityManager->persist($client);

        $existingReport = $this->buildReport($client);

        self::$entityManager->flush();
        self::$entityManager->refresh($existingReport);
        self::$entityManager->refresh($client);
        self::$entityManager->refresh($client->getUsers()[0]);

        $result = self::$sut->findAllActiveReportsByCaseNumbersAndRole(['4932965T'], $client->getUsers()[0]->getRoleName());
        self::assertEquals($existingReport, $result[0]);
    }
}
