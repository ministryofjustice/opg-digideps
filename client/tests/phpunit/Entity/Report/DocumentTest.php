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
    public function getPreviousReportPdfSubmission()
    {
        $report = new Report();

        $oldReportPdfDocument = (new Document())->setIsReportPdf(true)->setReport($report);
        $newReportPdfDocument = (new Document())->setIsReportPdf(true)->setReport($report);
        $supportingDocumentSubmittedWithReport = (new Document())->setIsReportPdf(false)->setReport($report);
        $supportingDocumentSubmittedAfterReport = (new Document())->setIsReportPdf(false)->setReport($report);

        $oldReportPdfSubmission = (new ReportSubmission())
            ->setDocuments([$oldReportPdfDocument, $supportingDocumentSubmittedWithReport])
            ->setCreatedOn(new DateTime());

        $newReportPdfSubmission = (new ReportSubmission())
            ->setDocuments([$newReportPdfDocument])
            ->setCreatedOn(new DateTime('+1 Day'));

        $supportingDocumentSubmission = (new ReportSubmission())
            ->setDocuments([$supportingDocumentSubmittedAfterReport])
            ->setCreatedOn(new DateTime());

        $oldReportPdfDocument->setReportSubmission($oldReportPdfSubmission);
        $newReportPdfDocument->setReportSubmission($newReportPdfSubmission);
        $supportingDocumentSubmittedWithReport->setReportSubmission($oldReportPdfSubmission);
        $supportingDocumentSubmittedAfterReport->setReportSubmission($supportingDocumentSubmission);

        $report->setReportSubmissions([$newReportPdfSubmission, $oldReportPdfSubmission, $supportingDocumentSubmission]);

        self::assertEquals($oldReportPdfSubmission, $oldReportPdfDocument->getPreviousReportPdfSubmission());
        self::assertEquals($oldReportPdfSubmission, $newReportPdfDocument->getPreviousReportPdfSubmission());
        self::assertEquals($oldReportPdfSubmission, $supportingDocumentSubmittedWithReport->getPreviousReportPdfSubmission());
        self::assertEquals($oldReportPdfSubmission, $supportingDocumentSubmittedAfterReport->getPreviousReportPdfSubmission());
    }
}
