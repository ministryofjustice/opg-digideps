<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Client;
use App\Repository\ReportSubmissionRepository;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\ApiTestCase;
use DateTime;
use App\Entity\Report\Document;
use App\Entity\Report\ReportSubmission;
use App\TestHelpers\ReportSubmissionHelper;
use App\TestHelpers\ReportTestHelper;

class ReportSubmissionRepositoryTest extends ApiTestCase
{
    private static ReportSubmissionHelper $reportSubmissionHelper;
    private static ReportSubmissionRepository $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$reportSubmissionHelper = new ReportSubmissionHelper(self::$entityManager);

        /** @var ReportSubmissionRepository $sut */
        $sut = self::$entityManager->getRepository(ReportSubmission::class);

        self::$sut = $sut;
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

    /**
     * @dataProvider updateArchivedStatusDataProvider
     */
    public function testUpdateArchivedStatus($isArchived, $docStatuses, $shouldArchive)
    {
        $client = new Client();
        $report = (ReportTestHelper::create())->generateReport(self::$entityManager);
        $user = (UserTestHelper::create())->createAndPersistUser(self::$entityManager, $client);

        $submission = new ReportSubmission($report, $user);
        $submission->setArchived($isArchived);
        self::$entityManager->persist($submission);

        foreach ($docStatuses as $docStatus) {
            $doc = new Document($report);
            $doc->setSynchronisationStatus($docStatus);
            $doc->setFileName('a file.pdf');
            self::$entityManager->persist($doc);

            $submission->addDocument($doc);
        }

        self::$entityManager->persist($submission);

        self::$entityManager->flush();

        self::$sut->updateArchivedStatus($submission);

        self::assertEquals($shouldArchive, $submission->getArchived());
    }

    public function testUpdateArchivedStatusManuallyArchived()
    {
        $submission = self::$reportSubmissionHelper->generateAndPersistReportSubmission();
        $submission->setArchived(false);

        $reportHelper = ReportTestHelper::create();

        for ($i = 0; $i < 2; $i++) {
            $report = $reportHelper->generateReport(self::$entityManager);
            self::$entityManager->persist($report);
            self::$entityManager->persist($report->getClient());

            $doc = new Document($report);
            $doc->setFileName('a file.pdf');
            self::$entityManager->persist($doc);

            $submission->addDocument($doc);
        }

        self::$entityManager->persist($submission);
        self::$entityManager->flush();

        self::$sut->updateArchivedStatus($submission);

        self::assertEquals(false, $submission->getArchived());
    }

    public function testFindAllReportSubmissions()
    {
        $today = new DateTime();
        $yesterday = new DateTime('-1 day');

        $createdReportSubmissions = [];
        for ($i = 1; $i <= 3; $i++) {
            $createdReportSubmissions[] = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($yesterday);
        }

        $reportSubmissions = self::$sut->findAllReportSubmissions($yesterday, $today);

        $this->assertEqualsCanonicalizing($createdReportSubmissions, $reportSubmissions);
    }

    public function testFindAllReportSubmissionsOnlyReturnsSubmissionsWithPeriodProvided()
    {
        $today = new DateTime();
        $todayOneHourAgo = new DateTime('-1 hour');
        $yesterday = new DateTime('-1 day');

        $todaysReportSubmissions = [];
        for ($i = 0; $i < 3; $i++) {
            $todaysReportSubmissions[] = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($today);
        }

        $yesterdaysReportSubmissions = [];
        for ($i = 0; $i < 3; $i++) {
            $yesterdaysReportSubmissions[] = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($yesterday);
        }

        $reportSubmissions = self::$sut->findAllReportSubmissions($yesterday, $todayOneHourAgo);

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
        for ($i = 0; $i < 3; $i++) {
            $reportSubmission = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($yesterday);
            $yesterdaysReportSubmissionsIds[] = $reportSubmission->getId();
        }

        $lastWeekReportSubmissionsIds = [];
        for ($i = 0; $i < 3; $i++) {
            $reportSubmission = self::$reportSubmissionHelper
                ->generateAndPersistSubmittedReportSubmission($lastWeek);
            $lastWeekReportSubmissionsIds[] = $reportSubmission->getId();
        }

        $reportSubmissions = self::$sut->findAllReportSubmissionsRawSql($threeDaysAgo, $today);

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
