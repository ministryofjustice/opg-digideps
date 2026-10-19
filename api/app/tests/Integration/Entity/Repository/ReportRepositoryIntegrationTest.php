<?php

namespace Tests\OPG\Digideps\Backend\Integration\Entity\Repository;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\Deputy\DeputyType;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use OPG\Digideps\Backend\Entity\Report\Checklist;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\SynchronisableInterface;
use OPG\Digideps\Backend\Repository\ReportRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class ReportRepositoryIntegrationTest extends ApiIntegrationTestCase
{
    private array $queryResult;
    private Checklist|array $queuedChecklists = [];
    public const int QUERY_LIMIT = 2;
    private static ReportRepository $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var ReportRepository $repo */
        $repo = self::$entityManager->getRepository(Report::class);

        self::$sut = $repo;

        // there is unindentified cross-contamination from other integration tests, so clear out the database before
        // these tests (this test runs on its own but not as part of the whole integration test suite)
        self::purgeDatabase();
    }

    private function ensureChecklistsExistInDatabase(): ReportRepositoryIntegrationTest
    {
        self::$fixtureService->flush();
        $this->queuedChecklists[] = $this->buildChecklistWithStatus(SynchronisableInterface::SYNC_STATUS_QUEUED);
        $this->queuedChecklists[] = $this->buildChecklistWithStatus(SynchronisableInterface::SYNC_STATUS_QUEUED);
        $this->queuedChecklists[] = $this->buildChecklistWithStatus(SynchronisableInterface::SYNC_STATUS_QUEUED);
        $this->buildChecklistWithStatus(SynchronisableInterface::SYNC_STATUS_SUCCESS);
        $this->buildChecklistWithStatus(null);

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

    private function buildChecklistWithStatus(?string $status): Checklist
    {
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        $checklist = new Checklist($report);

        if ($status) {
            $checklist->setSynchronisationStatus($status);
        }

        self::$fixtureService->persist($checklist);
        self::$fixtureService->flush();

        return $checklist;
    }

    public function testReportsAreSortedByDueDate(): void
    {
        $team = DeputySet::oneTeam(DeputyType::PRO, 'pro', 0, 1);
        ['persons' => $persons , 'orders' => [['pfa' => ['reports' => [$report1]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor($team)));
        ['persons' => $persons , 'orders' => [['pfa' => ['reports' => [$report2]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor($team)), $persons);
        ['persons' => $persons , 'orders' => [['pfa' => ['reports' => [$dualReport1]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor($team, single: false, siblingDeputySet: DeputySet::oneLay())), $persons);
        ['persons' => $persons , 'orders' => [['pfa' => ['reports' => [$dualReport2]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor($team, single: false, siblingDeputySet: DeputySet::oneLay())), $persons);

        $report1->setDueDate(new \DateTime('2025-08-01'))->setEndDate(new \DateTime('2025-07-10'));
        $report2->setDueDate(new \DateTime('2025-03-01'))->setEndDate(new \DateTime('2025-02-10'));
        $dualReport1->setDueDate(new \DateTime('2025-02-01'))->setEndDate(new \DateTime('2025-01-10'));
        $dualReport2->setDueDate(new \DateTime('2025-06-01'))->setEndDate(new \DateTime('2025-05-10'));

        self::$fixtureService->flush();

        $reports = self::$sut->getAllByUserId($persons['users']['pro-member-1']->getId(), new ParameterBag(), 'notStarted');

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
        ['client' => $client, 'persons' => ['users' => ['lay1' => $user]] , 'orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        $result = self::$sut->findAllActiveReportsByCaseNumbersAndRole([$client->getCaseNumber()], $user->getRoleName());
        self::assertSame($report->getId(), $result[0]->getId());
    }
}
