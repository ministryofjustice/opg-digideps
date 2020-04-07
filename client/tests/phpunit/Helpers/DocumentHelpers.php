<?php declare(strict_types=1);


namespace DigidepsTests\Helpers;


use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentHelpers extends TestCase
{
    private function generateDocument(
        string $documentType,
        string $caseRef,
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        int $reportPdfSubmissionId = 9876,
        int $supportingDocSubmissionId = 9877,
        int $documentId = 6789,
        ?string $submittedReportPdfUuid = null,
        string $storageReference = 'test',
        string $fileName
    )
    {
        $client = new Client();
        $client->setCaseNumber($caseRef);
        $reportPdfDocument = (new Document())->setIsReportPdf(true);
        $supportingDocument = (new Document())->setIsReportPdf(false);

        $reportPdfReportSubmission =
            (new ReportSubmission())
                ->setId($reportPdfSubmissionId)
                ->setUuid($submittedReportPdfUuid)
                ->setDocuments([$reportPdfDocument]);

        $supportingDocReportSubmission =
            (new ReportSubmission())
                ->setId($supportingDocSubmissionId)
                ->setDocuments([$supportingDocument]);

        $reportPdfDocument->setReportSubmission($reportPdfReportSubmission);
        $supportingDocument->setReportSubmission($supportingDocReportSubmission);

        $reportSubmissions = [$reportPdfReportSubmission, $supportingDocReportSubmission];

        $report = (new Report())
            ->setId(1)
            ->setType(Report::TYPE_102)
            ->setClient($client)
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setSubmitDate($submittedDate)
            ->setReportSubmissions($reportSubmissions);

        if ($documentType === 'reportPdf' && $submittedReportPdfUuid) {
            $report->setSubmittedDocuments([$reportPdfDocument]);
        } elseif ($documentType === 'supportingDocument' && ($submittedReportPdfUuid)) {
            $report->setSubmittedDocuments([$reportPdfDocument, $supportingDocument]);
        }
        $file = new UploadedFile("/","mypdf.pdf","application/pdf", 1024, 100);

        if ($documentType === 'reportPdf') {
            return $reportPdfDocument
                ->setReport($report)
                ->setStorageReference($storageReference)
                ->setFileName($fileName)
                ->setFile($file)
                ->setId($documentId);
        } elseif ($documentType === 'supportingDocument') {
            return $supportingDocument
                ->setReport($report)
                ->setStorageReference($storageReference)
                ->setFileName($fileName)
                ->setFile($file)
                ->setId($documentId);
        }

        return new \Exception('$documentType must be either reportPdf or supportingDocument');
    }

    public function generateSubmittedReportDocument(
        string $caseRef,
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        string $fileName = 'test.pdf',
        int $reportPdfSubmissionId = 9876,
        int $supportingDocSubmissionId = 9877,
        int $documentId = 6789,
        ?string $submittedReportPdfUuid = 'uuid-123-goes-here',
        string $storageReference = 'test'
    )
    {
        return $this->generateDocument(
            'reportPdf',
            $caseRef,
            $startDate,
            $endDate,
            $submittedDate,
            $reportPdfSubmissionId,
            $supportingDocSubmissionId,
            $documentId,
            $submittedReportPdfUuid,
            $storageReference,
            $fileName
    );
    }

    public function generateSubmittedSupportingDocument(
        string $caseRef,
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        int $reportPdfSubmissionId = 9876,
        int $supportingDocSubmissionId = 9877,
        int $documentId = 6789,
        ?string $submittedReportPdfUuid = 'uuid-123-goes-here',
        string $storageReference = 'test'
    )
    {
        return $this->generateDocument(
            'supportingDocument',
            $caseRef,
            $startDate,
            $endDate,
            $submittedDate,
            $reportPdfSubmissionId,
            $supportingDocSubmissionId,
            $documentId,
            $submittedReportPdfUuid,
            $storageReference
        );
    }
}
