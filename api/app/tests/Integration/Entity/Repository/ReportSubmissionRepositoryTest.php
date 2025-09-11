<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use DateTime;
use App\Entity\Report\Document;
use App\Entity\Report\ReportSubmission;
use App\TestHelpers\ReportSubmissionHelper;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Integration\ApiBaseTestCase;

class ReportSubmissionRepositoryTest extends ApiBaseTestCase
{
    private static ReportSubmissionHelper $reportSubmissionHelper;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$reportSubmissionHelper = new ReportSubmissionHelper(self::$staticEntityManager);
    }

    /**
     * @dataProvider updateArchivedStatusDataProvider
     */
    public function testUpdateArchivedStatus($isArchived, $docStatuses, $shouldArchive)
    {
        $submission = self::$reportSubmissionHelper->generateAndPersistReportSubmission();
        $submission->setArchived($isArchived);

        $reportHelper = ReportTestHelper::create();

        $docs = array_map(function ($status) use ($reportHelper) {
            $report = $reportHelper->generateReport($this->entityManager);
            $this->entityManager->persist($report);
            $this->entityManager->persist($report->getClient());

            return (new Document($report))
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

    public static function updateArchivedStatusDataProvider(): array
    {
        return [
            'One synced document' => [false, [Document::SYNC_STATUS_SUCCESS], true],
            'Two documents, one synced' => [false, [Document::SYNC_STATUS_SUCCESS, Document::SYNC_STATUS_PERMANENT_ERROR], false],
            'Two synced documents' => [false, [Document::SYNC_STATUS_SUCCESS, Document::SYNC_STATUS_SUCCESS], true],
            'Two synced documents, already archived' => [true, [Document::SYNC_STATUS_SUCCESS, Document::SYNC_STATUS_SUCCESS], true],
        ];
    }

    public function testUpdateArchivedStatusManuallyArchived()
    {
        $submission = self::$reportSubmissionHelper->generateAndPersistReportSubmission();
        $submission->setArchived(false);

        $reportHelper = ReportTestHelper::create();

        $statuses = [null, null];
        $docs = array_map(function () use ($reportHelper) {
            $report = $reportHelper->generateReport($this->entityManager);
            $this->entityManager->persist($report);
            $this->entityManager->persist($report->getClient());

            return (new Document($report))
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

    public function testFindAllReportSubmissions()
    {
        $today = new DateTime();
        $yesterday = new DateTime('-1 day');

        $createdReportSubmissions = [];
        foreach (range(1, 3) as $ignored) {
            $createdReportSubmissions[] = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($yesterday);
        }

        $reportSubmissions = $this->entityManager
            ->getRepository(ReportSubmission::class)
            ->findAllReportSubmissions($yesterday, $today);

        $this->assertEqualsCanonicalizing($createdReportSubmissions, $reportSubmissions);
    }

    public function testFindAllReportSubmissionsOnlyReturnsSubmissionsWithPeriodProvided()
    {
        $today = new DateTime();
        $todayOneHourAgo = new DateTime('-1 hour');
        $yesterday = new DateTime('-1 day');

        $todaysReportSubmissions = [];
        foreach (range(1, 3) as $index) {
            $todaysReportSubmissions[] = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($today);
        }

        $yesterdaysReportSubmissions = [];
        foreach (range(1, 3) as $index) {
            $yesterdaysReportSubmissions[] = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($yesterday);
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

    public function testFindAllReportSubmissionsRawSqlWithPeriodProvided()
    {
        $today = new DateTime();
        $yesterday = new DateTime('-1 day');
        $threeDaysAgo = new DateTime('-3 days');
        $lastWeek = new DateTime('-7 days');

        $yesterdaysReportSubmissionsIds = [];
        foreach (range(1, 3) as $i) {
            $reportSubmission = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($yesterday);
            $yesterdaysReportSubmissionsIds[] = $reportSubmission->getId();
        }

        $lastWeekReportSubmissionsIds = [];
        foreach (range(1, 3) as $i) {
            $reportSubmission = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($lastWeek);
            $lastWeekReportSubmissionsIds[] = $reportSubmission->getId();
        }

        $reportSubmissions = $this->entityManager
            ->getRepository(ReportSubmission::class)
            ->findAllReportSubmissionsRawSql($threeDaysAgo, $today);

        $actualReportSubmissionIds = [];
        foreach ($reportSubmissions as $reportSubmission) {
            $actualReportSubmissionIds[] = $reportSubmission['id'];
        }

        foreach ($yesterdaysReportSubmissionsIds as $rsid) {
            self::assertContains($rsid, $actualReportSubmissionIds);
        }

        foreach ($lastWeekReportSubmissionsIds as $rsid) {
            self::assertNotContains($rsid, $actualReportSubmissionIds);
        }
    }
}
