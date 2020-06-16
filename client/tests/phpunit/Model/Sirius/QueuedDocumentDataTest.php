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
}
