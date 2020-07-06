<?php declare(strict_types=1);

namespace Tests\AppBundle\Entity\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\ChecklistRepository;
use AppBundle\Entity\SynchronisableInterface;
use DateInterval;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChecklistRepositoyTest extends KernelTestCase
{
    /** @var ChecklistRepository */
    private $sut;

    /** @var EntityManager */
    private $entityManager;

    /** @var array */
    private $queryResult;

    /** @var int */
    const QUERY_LIMIT = 2;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->sut = $this->entityManager->getRepository(Checklist::class);
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
     * @return ChecklistRepositoyTest
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function ensureChecklistsExistInDatabase(): ChecklistRepositoyTest
    {
        $client = (new Client())->setCaseNumber('49329657');
        $this->entityManager->persist($client);

        $this
            ->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_QUEUED)
            ->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_QUEUED)
            ->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_QUEUED)
            ->buildChecklistWithStatus($client, SynchronisableInterface::SYNC_STATUS_SUCCESS)
            ->buildChecklistWithStatus($client, null);

        $this->entityManager->flush();

        return $this;
    }

    /**
     * @return ChecklistRepositoyTest
     * @throws \Doctrine\DBAL\DBALException
     */
    private function fetchChecklists(): ChecklistRepositoyTest
    {
        $this->queryResult = $this->sut->getQueuedAndSetToInProgress(self::QUERY_LIMIT);
        return $this;
    }

    /**
     * @return ChecklistRepositoyTest
     */
    private function assertOnlyAlimitedNumberOfQueuedChecklistsAreReturned(): ChecklistRepositoyTest
    {
        $this->assertCount(self::QUERY_LIMIT, $this->queryResult);
        $this->assertEquals(1, $this->queryResult[0]['id']);
        $this->assertEquals(2, $this->queryResult[1]['id']);

        return $this;
    }

    private function assertQueuedChecklistsAreUpdatedToInProgress(): void
    {
        $result = $this->sut->findBy(['synchronisationStatus' => SynchronisableInterface::SYNC_STATUS_IN_PROGRESS]);
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]->getId());
        $this->assertEquals(2, $result[1]->getId());
    }

    /**
     * @param Client $client
     * @param string|null $status
     * @return ChecklistRepositoyTest
     * @throws \Doctrine\ORM\ORMException
     */
    private function buildChecklistWithStatus(Client $client, ?string $status): ChecklistRepositoyTest
    {
        $report = $this->buildReport($client);
        $checklist = new Checklist($report);

        if ($status) {
            $checklist->setSynchronisationStatus($status);
        }

        $this->entityManager->persist($checklist);

        return $this;
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

        $this->entityManager->persist($report);

        return $report;
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
