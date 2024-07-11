<?php

namespace App\Service;

use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\ReportInterface;
use App\Exception\ReportSubmissionDocumentsNotDownloadableException;
use App\Service\Client\RestClient;
use App\Service\Csv\TransactionsCsvGenerator;
use App\Service\File\S3FileUploader;
use App\Service\Mailer\MailFactory;
use App\Service\Mailer\MailSenderInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class ReportSubmissionService
{
    public const MSG_NOT_DOWNLOADABLE = 'This report is not downloadable';
    public const MSG_NO_DOCUMENTS = 'No documents found for downloading';

    /**
     * @var S3FileUploader
     */
    private $fileUploader;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var Templating container
     */
    private $templating;

    /**
     * @var HtmlToPdfGenerator
     */
    private $htmltopdf;

    /**
     * @var MailSenderInterface
     */
    private $mailSender;

    /**
     * @var MailFactory
     */
    private $mailFactory;

    /**
     * @var TransactionsCsvGenerator
     */
    private $csvGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ReportSubmissionService constructor.
     *
     * @throws \Exception
     */
    public function __construct(
        TransactionsCsvGenerator $csvGenerator,
        Environment $templating,
        S3FileUploader $fileUploader,
        RestClient $restClient,
        LoggerInterface $logger,
        MailFactory $mailFactory,
        MailSenderInterface $mailSender,
        HtmlToPdfGenerator $htmltopdf
    ) {
        $this->fileUploader = $fileUploader;
        $this->restClient = $restClient;
        $this->mailSender = $mailSender;
        $this->mailFactory = $mailFactory;
        $this->templating = $templating;
        $this->htmltopdf = $htmltopdf;
        $this->logger = $logger;
        $this->csvGenerator = $csvGenerator;
    }

    /**
     * Wrapper method for all documents generated for a report submission.
     *
     * @param Report $report
     */
    public function generateReportDocuments(ReportInterface $report)
    {
        $this->generateReportPdf($report);
        if (in_array($report->getType(), Report::HIGH_ASSETS_REPORT_TYPES)) {
            if (!empty($report->getGifts()) || !empty($report->getExpenses()) || !empty($report->getMoneyTransactionsIn())
                || !empty($report->getMoneyTransactionsOut())) {
                $csvContent = $this->csvGenerator->generateTransactionsCsv($report);
                $this->fileUploader->uploadFileAndPersistDocument(
                    $report,
                    $csvContent,
                    $report->createAttachmentName('DigiRepTransactions-%s_%s_%s.csv'),
                    false
                );
            }
        }
    }

    /**
     * Generates the PDF of the report.
     *
     * @param Report $report
     */
    public function generateReportPdf(ReportInterface $report, bool $overwrite = false): void
    {
        // store PDF (with summary info) as a document
        $this->fileUploader->uploadFileAndPersistDocument(
            $report,
            $this->getPdfBinaryContent($report, true),
            $report->createAttachmentName('DigiRep-%s_%s_%s.pdf'),
            true,
            $overwrite
        );
    }

    /**
     * Generate the HTML of the report and convert to PDF.
     *
     * @param Report $report
     * @param bool   $showSummary
     *
     * @return string binary PDF content
     */
    public function getPdfBinaryContent(ReportInterface $report, $showSummary = false)
    {
        $html = $this->templating->render('@App/Report/Formatted/formatted_standalone.html.twig', [
            'report' => $report,
            'showSummary' => $showSummary,
        ]);

        if (false === ($pdf = $this->htmltopdf->getPdfFromHtml($html))) {
            $this->logger->error(sprintf('html_to_pdf_generation - failure - Error with pdf generation on report id: %d', $report->getId()));
        }

        return $pdf;
    }

    /**
     * @to-do move this into a checklist or pdf service
     * Generate the HTML of the report and convert to PDF
     *
     * @param Report $report
     *
     * @return string binary PDF content
     */
    public function getChecklistPdfBinaryContent(ReportInterface $report)
    {
        $reviewChecklist = $this->restClient->get('report/'.$report->getId().'/checklist', 'Report\\ReviewChecklist');

        // A null id indicates a reviewChecklist has not yet been submitted.
        if (null === $reviewChecklist->getId()) {
            $reviewChecklist = null;
        }

        $html = $this->templating->render('@App/Admin/Client/Report/Formatted/checklist_formatted_standalone.html.twig', [
            'report' => $report,
            'lodgingChecklist' => $report->getChecklist(),
            'reviewChecklist' => $reviewChecklist,
        ]);

        return $this->htmltopdf->getPdfFromHtml($html);
    }

    public function getReportSubmissionById(string $id)
    {
        return $this->restClient->get("report-submission/{$id}", 'Report\\ReportSubmission');
    }

    /**
     * @return array
     */
    public function getReportSubmissionsByIds(array $ids)
    {
        $reportSubmissions = [];

        foreach ($ids as $id) {
            /** @var ReportSubmission $reportSubmission */
            $reportSubmission = $this->getReportSubmissionById($id);
            $reportSubmissions[] = $reportSubmission;
        }

        return $reportSubmissions;
    }

    /**
     * @throws ReportSubmissionDocumentsNotDownloadableExceptionAlias
     */
    public function assertReportSubmissionIsDownloadable(ReportSubmission $reportSubmission)
    {
        if (true !== $reportSubmission->isDownloadable()) {
            throw new ReportSubmissionDocumentsNotDownloadableException(self::MSG_NOT_DOWNLOADABLE);
        }

        if (empty($reportSubmission->getDocuments())) {
            throw new ReportSubmissionDocumentsNotDownloadableException(self::MSG_NO_DOCUMENTS);
        }
    }
}
