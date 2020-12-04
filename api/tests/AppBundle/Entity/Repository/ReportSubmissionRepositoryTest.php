<?php

namespace Tests\AppBundle\Entity\Repository;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\Repository\ReportSubmissionRepository;
use AppBundle\TestHelpers\ReportSubmissionHelper;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Prophecy\Argument;
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
        $em = self::prophesize(EntityManagerInterface::class);
        $metaClass = self::prophesize(ClassMetadata::class);

        $docs = array_map(function ($status) {
            $doc = self::prophesize(Document::class);
            $doc->getSynchronisationStatus()->willReturn($status);
            return $doc;
        }, $docStatuses);

        $reportSubmission = self::prophesize(ReportSubmission::class);
        $reportSubmission->getDocuments()->shouldBeCalled()->willReturn($docs);
        $reportSubmission->getArchived()->shouldBeCalled()->willReturn($isArchived);

        if ($shouldArchive) {
            $reportSubmission->setArchived(true)->shouldBeCalled();
        } else {
            $reportSubmission->setArchived(Argument::any())->shouldNotBeCalled();
        }

        $sut = new ReportSubmissionRepository($em->reveal(), $metaClass->reveal());

        $sut->updateArchivedStatus($reportSubmission->reveal());
    }

    public function updateArchivedStatusDataProvider()
    {
        return [
            'Manual documents' => [false, [null, null], false],
            'One synced document' => [false, [Document::SYNC_STATUS_SUCCESS], true],
            'Two documents, one synced' => [false, [Document::SYNC_STATUS_SUCCESS, DOCUMENT::SYNC_STATUS_PERMANENT_ERROR], false],
            'Two synced documents' => [false, [Document::SYNC_STATUS_SUCCESS, Document::SYNC_STATUS_SUCCESS], true],
            'Two synced documents, already archived' => [true, [Document::SYNC_STATUS_SUCCESS, Document::SYNC_STATUS_SUCCESS], false],
        ];
    }

    /** @test */
    public function findAllReportSubmissions()
    {
        $today = new DateTime();
        $yesterday = new DateTime('-1 day');

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
    public function findAllReportSubmissions_only_returns_submissions_with_period_provided()
    {
        $today = new DateTime();
        $todayOneHourAgo = new DateTime('-1 hour');
        $yesterday = new DateTime('-1 day');

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
