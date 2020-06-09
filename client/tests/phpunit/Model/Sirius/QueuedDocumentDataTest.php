<?php declare(strict_types=1);


use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Model\Sirius\QueuedDocumentData;
use PHPUnit\Framework\TestCase;

class QueuedDocumentDataTest extends TestCase
{
    /**
     * @dataProvider supportingDocumentProvider
     * @test
     */
    public function supportingDocumentCanBeSynced(QueuedDocumentData $documentData1, QueuedDocumentData $documentData2, ?string $uuid, bool $expectedResult)
    {
        $reportSubmission = (new ReportSubmission())->setUuid($uuid);
        $documentData1->setReportSubmissions([$reportSubmission]);
        $documentData2->setReportSubmissions([$reportSubmission]);

        self::assertEquals($expectedResult, $documentData1->supportingDocumentCanBeSynced());
    }

    public function supportingDocumentProvider()
    {
        $reportPdfDocument = (new QueuedDocumentData())->setIsReportPdf(true);
        $supportingDocument = (new QueuedDocumentData())->setIsReportPdf(false);

        return [
            'Can be synced' => [$supportingDocument, $reportPdfDocument, 'abc-123-def-456', true],
            'Cannot be synced' => [$supportingDocument, $supportingDocument, null, false]
        ];
    }

    /** @test */
    public function getSyncedReportSubmission()
    {
        $document = (new Document())->setId(1);

        $syncedReportPdfSubmission = (new ReportSubmission())->setUuid('abc')->setDocuments([$document]);
        $syncedReportPdfDocumentData = (new QueuedDocumentData())
            ->setIsReportPdf(true)
            ->setReportSubmissions([$syncedReportPdfSubmission])
            ->setDocumentId($document->getId());;

        self::assertEquals($syncedReportPdfSubmission, $syncedReportPdfDocumentData->getSyncedReportSubmission());
    }

    /** @test */
    public function getSyncedReportSubmission_not_synced_documents_return_null()
    {
        $document = (new Document())->setId(1);

        $notSyncedReportPdfSubmission = (new ReportSubmission())->setUuid(null)->setDocuments([$document]);
        $notSyncedReportPdfDocumentData = (new QueuedDocumentData())
            ->setIsReportPdf(true)
            ->setReportSubmissions([$notSyncedReportPdfSubmission])
            ->setDocumentId($document->getId());

        self::assertEquals(null, $notSyncedReportPdfDocumentData->getSyncedReportSubmission());
    }

    /** @test */
    public function getSyncedReportSubmission_two_submissions_returns_submission_with_matching_document_id()
    {
        $document1 = (new Document())->setId(1);
        $document2 = (new Document())->setId(2);
        $document3 = (new Document())->setId(3);

        $syncedReportPdfSubmission1 = (new ReportSubmission())->setUuid('abc-123')->setDocuments([$document1, $document2]);
        $syncedReportPdfSubmission2 = (new ReportSubmission())->setUuid('def-345')->setDocuments([$document3]);

        $syncedReportPdfDocumentData = (new QueuedDocumentData())
            ->setIsReportPdf(true)
            ->setReportSubmissions([$syncedReportPdfSubmission1, $syncedReportPdfSubmission2])
            ->setDocumentId($document3->getId());

        self::assertEquals($syncedReportPdfSubmission2, $syncedReportPdfDocumentData->getSyncedReportSubmission());
    }
}
