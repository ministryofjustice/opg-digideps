<?php declare(strict_types=1);


namespace DigidepsTests\Helpers;


use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use DateTime;
use PHPUnit\Framework\TestCase;

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
        string $storageReference = 'test'
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

        if ($documentType === 'reportPdf') {
            return $reportPdfDocument
                ->setReport($report)
                ->setStorageReference($storageReference)
                ->setFileName('test.pdf')
                ->setFile(null)
                ->setId($documentId);
        } elseif ($documentType === 'supportingDocument') {
            return $supportingDocument
                ->setReport($report)
                ->setStorageReference($storageReference)
                ->setFileName('test.pdf')
                ->setFile(null)
                ->setId($documentId);
        }

        return new \Exception('$documentType must be either reportPdf or supportingDocument');
    }

    public function generateSubmittedReportDocument(
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
            'reportPdf',
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
