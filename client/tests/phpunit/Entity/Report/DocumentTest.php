<?php declare(strict_types=1);

namespace AppBundle\Entity\Report;


use PHPUnit\Framework\TestCase;


class DocumentTest extends TestCase
{
    /** @test */
    public function canBeSynced_report_pdf()
    {
        $report = new Report();
        $document = (new Document())->setReport($report)->setIsReportPdf(true);

        self::assertTrue($document->canBeSynced());
    }

    /**
     * @dataProvider supportingDocumentProvider
     * @test
     */
    public function canBeSynced_supporting_document(Document $document1, Document $document2, ?string $uuid, bool $expectedResult)
    {
        $reportSubmissions = [(new ReportSubmission())->setUuid($uuid)];
        $report = (new Report())->setSubmittedDocuments([$document1, $document2])->setReportSubmissions($reportSubmissions);

        $document1->setReport($report);
        self::assertEquals($expectedResult, $document1->canBeSynced());
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
}
