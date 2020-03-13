<?php declare(strict_types=1);


namespace DigidepsTests\Helpers;


use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use DateTime;

class DocumentHelpers
{
    static public function generateSubmittedReportDocument(
        string $caseRef,
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        int $reportSubmissionId = 9876,
        int $documentId = 6789,
        string $mimeType = 'application/pdf',
        string $fileName = 'test.pdf',
        string $storageReference = 'test'
    )
    {
        $client = new Client();
        $client->setCaseNumber($caseRef);

        $reportSubmissions = [(new ReportSubmission())->setId($reportSubmissionId)];

        $report = (new Report())
            ->setType(Report::TYPE_102)
            ->setClient($client)
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setSubmitDate($submittedDate)
            ->setReportSubmissions($reportSubmissions);

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
