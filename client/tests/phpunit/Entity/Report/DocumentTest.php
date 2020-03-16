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
    public function canBeSynced_supporting_document(Document $document1, Document $document2, bool $expectedResult)
    {
        $report = (new Report())->setSubmittedDocuments([$document1, $document2]);

        $document1->setReport($report);
        self::assertEquals($expectedResult, $document1->canBeSynced());
    }

    public function supportingDocumentProvider()
    {
        return [
            'Can be synced' => [(new Document())->setIsReportPdf(false), (new Document())->setIsReportPdf(true), true],
            'Cannot be synced' => [(new Document())->setIsReportPdf(false), (new Document())->setIsReportPdf(false), false]
        ];
    }
}
