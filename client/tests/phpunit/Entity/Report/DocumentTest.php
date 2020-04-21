<?php declare(strict_types=1);

namespace AppBundle\Entity\Report;


use DateTime;
use PHPUnit\Framework\TestCase;


class DocumentTest extends TestCase
{
    /**
     * @dataProvider supportingDocumentProvider
     * @test
     */
    public function supportingDocumentCanBeSynced(Document $document1, Document $document2, ?string $uuid, bool $expectedResult)
    {
        $reportSubmission = (new ReportSubmission())->setUuid($uuid);
        $document1->setReportSubmission($reportSubmission);
        $document2->setReportSubmission($reportSubmission);

        $report = (new Report())->setDocuments([$document1, $document2])->setReportSubmissions([$reportSubmission]);

        $document1->setReport($report);
        self::assertEquals($expectedResult, $document1->supportingDocumentCanBeSynced());
    }

    public function supportingDocumentProvider()
    {
        $reportPdfDocument = (new Document())->setIsReportPdf(true);
        $supportingDocument = (new Document())->setIsReportPdf(false);

        return [
            'Can be synced' => [$supportingDocument, $reportPdfDocument, 'abc-123-def-456', true],
            'Cannot be synced' => [$supportingDocument, $supportingDocument, null, false]
        ];
    }

    /** @test */
    public function getSyncedReportSubmission()
    {
        $report = new Report();

        $syncedReportPdfDocument = (new Document())->setIsReportPdf(true)->setReport($report);
        $notSyncedReportPdfDocument = (new Document())->setIsReportPdf(true)->setReport($report);

        $syncedReportPdfSubmission = (new ReportSubmission())->setDocuments([$syncedReportPdfDocument])->setUuid('abc');
        $notSyncedReportPdfSubmission = (new ReportSubmission())->setDocuments([$notSyncedReportPdfDocument]);

        $syncedReportPdfDocument->setReportSubmission($syncedReportPdfSubmission);
        $notSyncedReportPdfDocument->setReportSubmission($notSyncedReportPdfSubmission);

        $report->setReportSubmissions([$notSyncedReportPdfSubmission, $syncedReportPdfSubmission]);

        self::assertEquals($syncedReportPdfSubmission, $syncedReportPdfDocument->getSyncedReportSubmission());
        self::assertEquals($syncedReportPdfSubmission, $notSyncedReportPdfDocument->getSyncedReportSubmission());
    }
}
