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
    private function generateDocument(
        string $documentType,
        string $caseRef,
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        int $reportPdfSubmissionId = 9876,
        int $supportingDocSubmissionId = 9877,
        int $documentId = 6789,
        ?string $submittedReportPdfUuid = 'uuid-123-goes-here',
        ?string $submittedSupportingDocUuid = 'uuid-321-goes-here',
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
                ->setUuid($submittedSupportingDocUuid)
                ->setDocuments([$supportingDocument]);

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
        } elseif ($documentType === 'supportingDocument' && $submittedSupportingDocUuid) {
            $report->setSubmittedDocuments([$supportingDocument]);
        }

        $uploadedFile = FileHelpers::generateUploadedFile(
            'tests/phpunit/TestData/test.pdf',
            'test.pdf',
            'application/pdf'
        );

        if ($documentType === 'reportPdf') {
            return $reportPdfDocument
                ->setReport($report)
                ->setStorageReference($storageReference)
                ->setFileName('test.pdf')
                ->setFile($uploadedFile)
                ->setId($documentId);
        } elseif ($documentType === 'supportingDocument') {
            return $supportingDocument
                ->setReport($report)
                ->setStorageReference($storageReference)
                ->setFileName('test.pdf')
                ->setFile($uploadedFile)
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
        ?string $submittedSupportingDocUuid = 'uuid-321-goes-here',
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
            $submittedSupportingDocUuid,
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
        ?string $submittedSupportingDocUuid = 'uuid-321-goes-here',
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
            $submittedSupportingDocUuid,
            $storageReference
        );

//        $client = new Client();
//        $client->setCaseNumber($caseRef);
//        $reportPdfDocument = (new Document())->setIsReportPdf(true);
//        $supportingDocument = (new Document())->setIsReportPdf(false);
//
//        $reportPdfReportSubmission =
//            (new ReportSubmission())
//                ->setId($reportPdfSubmissionId)
//                ->setUuid($submittedReportPdfUuid)
//                ->setDocuments([$reportPdfDocument]);
//
//        $supportingDocReportSubmission =
//            (new ReportSubmission())
//                ->setId($supportingDocSubmissionId)
//                ->setUuid($submittedSupportingDocUuid)
//                ->setDocuments([$supportingDocument]);
//
//        $reportSubmissions = [$reportPdfReportSubmission, $supportingDocReportSubmission];
//
//        $report = (new Report())
//            ->setId(1)
//            ->setType(Report::TYPE_102)
//            ->setClient($client)
//            ->setStartDate($startDate)
//            ->setEndDate($endDate)
//            ->setSubmitDate($submittedDate)
//            ->setReportSubmissions($reportSubmissions);
//
//        $uploadedFile = FileHelpers::generateUploadedFile(
//            'tests/phpunit/TestData/test.pdf',
//            'test.pdf',
//            'application/pdf'
//        );
//
//        return $supportingDocument
//            ->setReport($report)
//            ->setStorageReference($storageReference)
//            ->setFileName('test.pdf')
//            ->setFile($uploadedFile)
//            ->setId($documentId);
    }
}
