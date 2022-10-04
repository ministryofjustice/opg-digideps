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
     * @test
     */
    public function synchroniseDocuments()
    {
        $documents = $this->documentProvider();
        $verboseLogger = self::prophesize(LoggerInterface::class);
        $docSyncService = self::prophesize(DocumentSyncService::class);

        $sut = new DocumentsSynchronisedSubscriber($verboseLogger->reveal(), $docSyncService->reveal());

        $event = new DocumentsSynchronisedEvent($documents);

        $expectedEvent = [
            'documents' => $documents,
        ];

        $verboseLogger->notice('', $expectedEvent)->shouldBeCalled();
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

        return $arrayOfDocuments;
    }
}
