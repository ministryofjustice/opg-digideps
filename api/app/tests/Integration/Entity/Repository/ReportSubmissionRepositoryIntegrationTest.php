<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Entity\Repository;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\ReportDescriptor;
use OPG\Digideps\Backend\Fixture\ReportList;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Backend\Repository\ReportSubmissionRepository;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use Tests\OPG\Digideps\Backend\Integration\Fixtures;

class ReportSubmissionRepositoryIntegrationTest extends ApiIntegrationTestCase
{
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
    public function testUpdateArchivedStatus(bool $isArchived, array $docStatuses, bool $shouldArchive)
    {
        self::$entityManager->clear();
        Fixtures::deleteReportsData();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())));
        $submission = $report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");
        self::$fixtureService->persist($submission->setArchived($isArchived));
        foreach ($docStatuses as $docStatus) {
            $doc = new Document($report, 'a file.pdf');
            $doc->setSynchronisationStatus($docStatus);
            $doc->setReportSubmission($submission);
            self::$fixtureService->persist($doc);
        }
        self::$fixtureService->flush();

        /** @var ReportSubmissionRepository $sut */
        $sut = self::$entityManager->getRepository(ReportSubmission::class);
        $sut->clear();
        $submission = $sut->findOneByIdUnfiltered($submission->getId()) ?? throw new \LogicException('This must exist');
        $sut->updateArchivedStatus($submission);
        self::assertSame($shouldArchive, $submission->getArchived());
    }

    public function testUpdateArchivedStatusManuallyArchived()
    {
        self::$entityManager->clear();
        Fixtures::deleteReportsData();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())));
        $submission = $report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");
        self::$fixtureService->persist($submission->setArchived(false));

        $doc = new Document($report, 'a file.pdf');
        $doc->setReportSubmission($submission);
        self::$fixtureService->persist($doc);
        self::$entityManager->flush();

        /** @var ReportSubmissionRepository $sut */
        $sut = self::$entityManager->getRepository(ReportSubmission::class);
        $sut->clear();
        $submission = $sut->findOneByIdUnfiltered($submission->getId()) ?? throw new \LogicException('This must exist');
        $sut->updateArchivedStatus($submission);

        self::assertFalse($submission->getArchived());
    }

    public function testFindAllReportSubmissions()
    {
        $today = new \DateTime();
        $yesterday = new \DateTimeImmutable('-1 day');
        $madeDate = new \DateTimeImmutable('-2 year');

        self::$entityManager->clear();
        Fixtures::deleteReportsData();

        /** @var ReportSubmissionRepository $sut */
        $sut = self::$entityManager->getRepository(ReportSubmission::class);
        $reportSubmissions = $sut->findAllReportSubmissions(\DateTime::createFromImmutable($yesterday), $today);
        $this->assertEmpty($reportSubmissions);

        $reportSubmissionIds = [];
        $scenario = new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), madeDate: $madeDate, reportList: new ReportList(false, new ReportDescriptor($madeDate->add(new \DateInterval('P1Y')), submitDate: $yesterday), new ReportDescriptor($madeDate))));
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $reportSubmissionIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $reportSubmissionIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $reportSubmissionIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();

        $foundReportSubmissionIds = array_map(fn (ReportSubmission $submission) => $submission->getId(), $sut->findAllReportSubmissions(\DateTime::createFromImmutable($yesterday), $today));

        $this->assertEqualsCanonicalizing($reportSubmissionIds, $foundReportSubmissionIds);
    }

    public function testFindAllReportSubmissionsOnlyReturnsSubmissionsWithPeriodProvided()
    {
        $today = new \DateTimeImmutable();
        $todayOneHourAgo = new \DateTime('-1 hour');
        $yesterday = new \DateTimeImmutable('-1 day');
        $madeDate = new \DateTimeImmutable('-2 year');

        $todaysReportSubmissionIds = [];
        $scenario = new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), madeDate: $madeDate, reportList: new ReportList(false, new ReportDescriptor($madeDate->add(new \DateInterval('P1Y')), submitDate: $today), new ReportDescriptor($madeDate))));
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $todaysReportSubmissionIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $todaysReportSubmissionIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $todaysReportSubmissionIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();

        $yesterdaysReportSubmissionIds = [];
        $scenario = new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), madeDate: $madeDate, reportList: new ReportList(false, new ReportDescriptor($madeDate->add(new \DateInterval('P1Y')), submitDate: $yesterday), new ReportDescriptor($madeDate))));
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $yesterdaysReportSubmissionIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $yesterdaysReportSubmissionIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $yesterdaysReportSubmissionIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();

        /** @var ReportSubmissionRepository $sut */
        $sut = self::$entityManager->getRepository(ReportSubmission::class);
        $reportSubmissionsIds = array_map(fn (ReportSubmission $submission) => $submission->getId(), $sut->findAllReportSubmissions(\DateTime::createFromImmutable($yesterday), $todayOneHourAgo));

        foreach ($yesterdaysReportSubmissionIds as $rs) {
            self::assertContains($rs, $reportSubmissionsIds);
        }

        foreach ($todaysReportSubmissionIds as $rs) {
            self::assertNotContains($rs, $reportSubmissionsIds);
        }
    }

    public function testFindAllReportSubmissionsRawSqlWithPeriodProvided()
    {
        $today = new \DateTime();
        $yesterday = new \DateTimeImmutable('-1 day');
        $threeDaysAgo = new \DateTime('-3 days');
        $lastWeek = new \DateTimeImmutable('-7 days');
        $madeDate = new \DateTimeImmutable('-2 year');

        $yesterdaysReportSubmissionsIds = [];
        $scenario = new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), madeDate: $madeDate, reportList: new ReportList(false, new ReportDescriptor($madeDate->add(new \DateInterval('P1Y')), submitDate: $yesterday), new ReportDescriptor($madeDate))));
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $yesterdaysReportSubmissionsIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $yesterdaysReportSubmissionsIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $yesterdaysReportSubmissionsIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();

        $lastWeekReportSubmissionsIds = [];
        $scenario = new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), madeDate: $madeDate, reportList: new ReportList(false, new ReportDescriptor($madeDate->add(new \DateInterval('P1Y')), submitDate: $lastWeek), new ReportDescriptor($madeDate))));
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $lastWeekReportSubmissionsIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $lastWeekReportSubmissionsIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();
        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario($scenario);
        $lastWeekReportSubmissionsIds[] = ($report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist"))->getId();

        /** @var ReportSubmissionRepository $sut */
        $sut = self::$entityManager->getRepository(ReportSubmission::class);
        $reportSubmissions = $sut->findAllReportSubmissionsRawSql($threeDaysAgo, $today);

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
