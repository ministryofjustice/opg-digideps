<?php declare(strict_types=1);

namespace AppBundle\Entity\Report;


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

        $report = (new Report())->setSubmittedDocuments([$document1, $document2])->setReportSubmissions([$reportSubmission]);

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
    public function getReportPdfSubmission()
    {
        $report = new Report();

        $reportPdfDocument = (new Document())->setIsReportPdf(true)->setReport($report);
        $supportingDocumentSubmittedWithReport = (new Document())->setIsReportPdf(false)->setReport($report);
        $supportingDocumentSubmittedAfterReport = (new Document())->setIsReportPdf(false)->setReport($report);

        $reportPdfSubmission = (new ReportSubmission())->setDocuments([$reportPdfDocument, $supportingDocumentSubmittedWithReport]);
        $supportingDocumentSubmission = (new ReportSubmission())->setDocuments([$supportingDocumentSubmittedAfterReport]);

        $reportPdfDocument->setReportSubmission($reportPdfSubmission);
        $supportingDocumentSubmittedWithReport->setReportSubmission($reportPdfSubmission);
        $supportingDocumentSubmittedAfterReport->setReportSubmission($supportingDocumentSubmission);

        $report->setReportSubmissions([$reportPdfSubmission, $supportingDocumentSubmission]);

        self::assertEquals($reportPdfSubmission, $reportPdfDocument->getReportPdfSubmission());
        self::assertEquals($reportPdfSubmission, $supportingDocumentSubmittedWithReport->getReportPdfSubmission());
        self::assertEquals($reportPdfSubmission, $supportingDocumentSubmittedAfterReport->getReportPdfSubmission());
    }
}
