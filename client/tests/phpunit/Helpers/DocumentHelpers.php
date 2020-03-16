<?php declare(strict_types=1);


namespace DigidepsTests\Helpers;


use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class DocumentHelpers extends TestCase
{
    public function generateSubmittedReportDocument(
        string $caseRef,
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        int $reportSubmissionId = 9876,
        int $documentId = 6789,
        bool $hasSubmittedReportPdf = true,
        string $mimeType = 'application/pdf',
        string $fileName = 'test.pdf',
        string $storageReference = 'test'
    )
    {
        $client = new Client();
        $client->setCaseNumber($caseRef);

        $reportSubmissions = [(new ReportSubmission())->setId($reportSubmissionId)];

        /** @var Report&ObjectProphecy $report */
        $report = new Report();

        $report->setId(1);
        $report->setType(Report::TYPE_102);
        $report->setClient($client);
        $report->setStartDate($startDate);
        $report->setEndDate($endDate);
        $report->setSubmitDate($submittedDate);
        $report->setReportSubmissions($reportSubmissions);

        $relatedDocument = self::prophesize(Document::class);
        $relatedDocument->getReportId()->willReturn(1);

        if ($hasSubmittedReportPdf) {
            $relatedDocument->isReportPdf()->willReturn(true);
        } else {
            $relatedDocument->isReportPdf()->willReturn(false);
        }

        $report->setSubmittedDocuments([$relatedDocument->reveal()]);

        $uploadedFile = FileHelpers::generateUploadedFile(
            'tests/phpunit/TestData/test.pdf',
            $fileName,
            $mimeType
        );

        return (new Document())
            ->setReport($report)
            ->setStorageReference($storageReference)
            ->setFileName($fileName)
            ->setFile($uploadedFile)
            ->setId($documentId);
    }
}
