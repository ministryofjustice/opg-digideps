<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Event\DocumentsSynchronisedEvent;
use App\EventSubscriber\DocumentsSynchronisedSubscriber;
use App\Model\Sirius\QueuedDocumentData;
use App\Service\DocumentSyncService;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class DocumentsSynchronisedSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals(
            [DocumentsSynchronisedEvent::NAME => 'synchroniseDocuments'],
            DocumentsSynchronisedSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider documentProvider
     *
     * @test
     */
    public function synchroniseDocuments(array $documents, int $failCount, array $submissionError)
    {
        $verboseLogger = self::prophesize(LoggerInterface::class);
        $docSyncService = self::prophesize(DocumentSyncService::class);

        $docSyncService->getDocsNotSyncedCount()->willReturn($failCount);
        $docSyncService->getSyncErrorSubmissionIds()->willReturn($submissionError);
        $docSyncService->syncDocument($documents[0])->willReturn($documents[0]);

        if (count($submissionError) > 0) {
            $docSyncService->setSubmissionsDocumentsToPermanentError()->shouldBeCalled();
            $docSyncService->setSyncErrorSubmissionIds([])->shouldBeCalled();
        } else {
            $docSyncService->setSubmissionsDocumentsToPermanentError()->shouldNotBeCalled();
            $docSyncService->setSyncErrorSubmissionIds([])->shouldNotBeCalled();
        }

        if ($failCount > 0) {
            $docSyncService->setDocsNotSyncedCount(0)->shouldBeCalled();
            $verboseLogger->notice(sprintf('%d documents failed to sync', $failCount))->shouldBeCalled();
        } else {
            $docSyncService->setDocsNotSyncedCount(0)->shouldNotBeCalled();
            $verboseLogger->notice(sprintf('%d documents failed to sync', $failCount))->shouldNotBeCalled();
        }

        $sut = new DocumentsSynchronisedSubscriber($verboseLogger->reveal(), $docSyncService->reveal());

        $event = new DocumentsSynchronisedEvent($documents);

        $sut->synchroniseDocuments($event);
    }

    public function documentProvider()
    {
        $queuedDocumentData = (new QueuedDocumentData())
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId(9876)
            ->setReportSubmissions([(new ReportSubmission())
                ->setId(9876)
                ->setUuid('5a8b1a26-8296-4373-ae61-f8d0b250e123'), ])
            ->setReportStartDate(new DateTime('2018-05-14'))
            ->setReportEndDate(new DateTime('2019-05-13'))
            ->setReportSubmitDate(new DateTime('2019-06-20'))
            ->setFilename('test.pdf')
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567t')
            ->setNdrId(null)
            ->setStorageReference('dd_doc_98765_01234567890123');
        $arrayOfDocuments = [
            $queuedDocumentData,
        ];

        return [
            'All Sync' => [$arrayOfDocuments, 0, []],
            'Sync Errors' => [$arrayOfDocuments, 1, []],
            'Sync and SubmissionId Errors' => [$arrayOfDocuments, 1, [9876]],
            'SubmissionId Errors' => [$arrayOfDocuments, 0, [9876]],
        ];
    }
}
