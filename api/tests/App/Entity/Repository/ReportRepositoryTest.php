<?php

namespace Tests\App\Entity\Repository;

use App\Entity\Client;
use App\Entity\ClientInterface;
use App\Entity\Report\Checklist;
use App\Entity\Report\Fee;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\Repository\ReportRepository;
use App\Entity\SynchronisableInterface;
use App\Entity\User;
use DateInterval;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery\MockInterface;
use Tests\ApiBaseTestCase;
use Mockery as m;

class ReportRepositoryTest extends ApiBaseTestCase
{
    /** @var ReportRepository */
    private $sut;

    /** @var Report | MockInterface */
    private $mockReport;

    /** @var EntityManagerInterface | MockInterface */
    private $mockEm;

    /** @var ClientInterface | MockInterface */
    private $mockClient;

    private $mockMetaClass;

    /** @var ReportRepository */
    private $repository;

    /** @var array */
    private $queryResult;

    /** @var []Checklist */
    private $queuedChecklists = [];

    /** @var int */
    const QUERY_LIMIT = 2;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDatabase();

        $this->mockEm = m::mock(EntityManagerInterface::class);
        $this->mockMetaClass = m::mock(ClassMetadata::class);
        $this->mockReport = m::mock(Report::class);
        $this->mockClient = m::mock(ClientInterface::class);

        $this->mockReport->shouldReceive('getClient')
            ->zeroOrMoreTimes()
            ->andReturn($this->mockClient);

        $this->sut = new ReportRepository($this->mockEm, $this->mockMetaClass);
        $this->repository = $this->entityManager->getRepository(Report::class);
    }

    public function testAddFeesToReportIfMissingForNonPAUser()
    {
        $this->mockReport->shouldReceive('isPAReport')->andReturn(false);

        $this->assertNull($this->sut->addFeesToReportIfMissing($this->mockReport));
    }

    public function testAddFeesToReportIfMissingForPAUserWithFeesMissing()
    {
        $this->mockReport->shouldReceive('getFees')->andReturn([]);

        $this->mockReport->shouldReceive('addFee')->times(count(Fee::$feeTypeIds))->andReturnSelf();

        $this->mockEm->shouldReceive('persist')->times(count(Fee::$feeTypeIds));

        $this->mockReport->shouldReceive('isPAReport')->andReturn(true);

        $this->assertEquals(7, $this->sut->addFeesToReportIfMissing($this->mockReport));
    }

    public function testAddFeesToReportIfMissingForPAUserWithFeesNotMissing()
    {
        $this->mockReport->shouldReceive('getFees')->andReturn(['foo']);

        $this->mockReport->shouldReceive('addFee')->never();

        $this->mockEm->shouldReceive('persist')->never();

        $this->mockReport->shouldReceive('isPAReport')->andReturn(true);

        $this->assertEquals(0, $this->sut->addFeesToReportIfMissing($this->mockReport));
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

    /**
     * @return ReportRepositoryTest
     * @throws \Doctrine\ORM\ORMException
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
     * @return ReportRepositoryTest
     * @throws \Doctrine\DBAL\DBALException
     */
    private function fetchChecklists(): ReportRepositoryTest
    {
        $this->queryResult = $this->repository->getReportsIdsWithQueuedChecklistsAndSetChecklistsToInProgress(self::QUERY_LIMIT);
        return $this;
    }

    /**
     * @return ReportRepositoryTest
     */
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
     * @param Client $client
     * @param string|null $status
     * @return ReportRepositoryTest
     * @throws \Doctrine\ORM\ORMException
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
     * @param Client $client
     * @return Report
     * @throws \Exception
     */
    private function buildReport(Client $client): Report
    {
        $startDate = new \DateTime('now', new DateTimeZone('UTC'));
        $endDate = $startDate->add(new DateInterval('P1D'));
        $report = new Report($client, Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS, $startDate, $endDate);

        $user = (new User())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setEmail(sprintf('email%s@test.com', rand(1, 1000)))
            ->setPassword('password');

        $reportSubmission = new ReportSubmission($report, $user);
        $report->addReportSubmission($reportSubmission);

        $this->entityManager->persist($report);
        $this->entityManager->persist($reportSubmission);
        $this->entityManager->persist($user);

        return $report;
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
