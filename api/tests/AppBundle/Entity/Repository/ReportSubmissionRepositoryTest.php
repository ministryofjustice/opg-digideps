<?php

namespace Tests\AppBundle\Entity\Repository;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\Repository\ReportSubmissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReportSubmissionRepositoryTest extends WebTestCase
{
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
}
