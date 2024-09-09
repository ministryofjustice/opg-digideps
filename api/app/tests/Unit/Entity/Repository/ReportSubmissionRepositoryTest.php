<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Report\Document;
use App\Entity\Report\ReportSubmission;
use App\TestHelpers\ReportSubmissionHelper;
use App\TestHelpers\ReportTestHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportSubmissionRepositoryTest extends WebTestCase
{
    private EntityManager $entityManager;
    private ReportSubmissionHelper $reportSubmissionHelper;

    public function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->reportSubmissionHelper = (new ReportSubmissionHelper());
    }

    /**
     * @dataProvider updateArchivedStatusDataProvider
     */
    public function testUpdateArchivedStatus($isArchived, $docStatuses, $shouldArchive)
    {
        $submission = $this->reportSubmissionHelper->generateAndPersistReportSubmission($this->entityManager);
        $submission->setArchived($isArchived);

        $reportHelper = new ReportTestHelper();

        $docs = array_map(function ($status) use ($reportHelper) {
            $report = $reportHelper->generateReport($this->entityManager);
            $this->entityManager->persist($report);
            $this->entityManager->persist($report->getClient());

            return ( new Document($report))
                ->setSynchronisationStatus($status)
                ->setFileName('a file.pdf');
        }, $docStatuses);

        foreach ($docs as $doc) {
            $submission->addDocument($doc);
            $this->entityManager->persist($doc);
        }

        $this->entityManager->flush();

        $sut = $this->entityManager->getRepository(ReportSubmission::class);

        $sut->updateArchivedStatus($submission);
        self::assertEquals($shouldArchive, $submission->getArchived());
    }

    public function updateArchivedStatusDataProvider()
    {
        return [
            'One synced document' => [false, [Document::SYNC_STATUS_SUCCESS], true],
            'Two documents, one synced' => [false, [Document::SYNC_STATUS_SUCCESS, DOCUMENT::SYNC_STATUS_PERMANENT_ERROR], false],
            'Two synced documents' => [false, [Document::SYNC_STATUS_SUCCESS, Document::SYNC_STATUS_SUCCESS], true],
            'Two synced documents, already archived' => [true, [Document::SYNC_STATUS_SUCCESS, Document::SYNC_STATUS_SUCCESS], true],
        ];
    }

    public function testUpdateArchivedStatusManuallyArchived()
    {
        $submission = $this->reportSubmissionHelper->generateAndPersistReportSubmission($this->entityManager);
        $submission->setArchived(false);

        $reportHelper = new ReportTestHelper();

        $statuses = [null, null];
        $docs = array_map(function ($status) use ($reportHelper) {
            $report = $reportHelper->generateReport($this->entityManager);
            $this->entityManager->persist($report);
            $this->entityManager->persist($report->getClient());

            return ( new Document($report))
                ->setFileName('a file.pdf');
        }, $statuses);

        foreach ($docs as $doc) {
            $submission->addDocument($doc);
            $this->entityManager->persist($doc);
        }

        $this->entityManager->flush();

        $sut = $this->entityManager->getRepository(ReportSubmission::class);

        $sut->updateArchivedStatus($submission);
        self::assertEquals(false, $submission->getArchived());
    }

    /** @test */
    public function findAllReportSubmissions()
    {
        $today = new \DateTime();
        $yesterday = new \DateTime('-1 day');

        $createdReportSubmissions = [];
        foreach (range(1, 3) as $index) {
            $createdReportSubmissions[] = $this->reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($this->entityManager, $yesterday);
        }

        $reportSubmissions = $this->entityManager
            ->getRepository(ReportSubmission::class)
            ->findAllReportSubmissions($yesterday, $today);

        $this->assertEqualsCanonicalizing($createdReportSubmissions, $reportSubmissions);
    }

    /** @test */
    public function findAllReportSubmissionsOnlyReturnsSubmissionsWithPeriodProvided()
    {
        $today = new \DateTime();
        $todayOneHourAgo = new \DateTime('-1 hour');
        $yesterday = new \DateTime('-1 day');

        $todaysReportSubmissions = [];
        foreach (range(1, 3) as $index) {
            $todaysReportSubmissions[] = $this->reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($this->entityManager, $today);
        }

        $yesterdaysReportSubmissions = [];
        foreach (range(1, 3) as $index) {
            $yesterdaysReportSubmissions[] = $this->reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($this->entityManager, $yesterday);
        }

        $reportSubmissions = $this->entityManager
            ->getRepository(ReportSubmission::class)
            ->findAllReportSubmissions($yesterday, $todayOneHourAgo);

        foreach ($yesterdaysReportSubmissions as $rs) {
            self::assertContains($rs, $reportSubmissions);
        }

        foreach ($todaysReportSubmissions as $rs) {
            self::assertNotContains($rs, $reportSubmissions);
        }
    }
}
