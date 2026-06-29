<?php

namespace OPG\Digideps\Frontend\Service;

use OPG\Digideps\Frontend\Component\Review\ReportReviewViews;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\ReportSubmission;
use OPG\Digideps\Frontend\Exception\ReportSubmissionDocumentsNotDownloadableException;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\Csv\TransactionsCsvGenerator;
use OPG\Digideps\Frontend\Service\File\S3FileUploader;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\Error;

class ReportSubmissionService
{
    public const string MSG_NOT_DOWNLOADABLE = 'This report is not downloadable';
    public const string MSG_NO_DOCUMENTS = 'No documents found for downloading';

    /**
     * ReportSubmissionService constructor.
     *
     * @throws \Exception
     */
    public function __construct(
        private readonly TransactionsCsvGenerator $csvGenerator,
        private readonly Environment $templating,
        private readonly S3FileUploader $fileUploader,
        private readonly RestClient $restClient,
        private readonly LoggerInterface $logger,
        private readonly HtmlToPdfGenerator $htmltopdf,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Wrapper method for all documents generated for a report submission.
     */
    public function generateReportDocuments(Report $report): void
    {
        $this->generateReportPdf($report);

        if (in_array($report->getType(), Report::HIGH_ASSETS_REPORT_TYPES)) {
            if (
                !empty($report->getGifts()) ||
                !empty($report->getExpenses()) ||
                !empty($report->getMoneyTransactionsIn()) ||
                !empty($report->getMoneyTransactionsOut())
            ) {
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
     */
    public function generateReportPdf(Report $report, bool $overwrite = false): void
    {
        $pdf = $this->getPdfBinaryContent($report, true);

        if ($pdf !== false) {
            // store PDF (with summary info) as a document
            $this->fileUploader->uploadFileAndPersistDocument(
                $report,
                $pdf,
                $report->createAttachmentName('DigiRep-%s_%s_%s.pdf'),
                true,
                $overwrite
            );
        } else {
            $this->logger->error('Unable to generate PDF for report with ID ' . $report->getId());
        }
    }

    /**
     * Generate the HTML of the report and convert to PDF.
     */
    public function getPdfBinaryContent(Report $report, bool $showSummary = false, bool $devPreview = false): string|false
    {
        $html = $this->templating->render($devPreview ? '@App/Report/Rendered/standalone.html.twig' : '@App/Report/Formatted/formatted_standalone.html.twig', [
            'report' => $report,
            'review' => new ReportReviewViews($report, $this->translator),
            'showSummary' => $showSummary,
        ]);

        $pdf = $this->htmltopdf->getPdfFromHtml($html);

        if (!$pdf) {
            $this->logger->error(sprintf('html_to_pdf_generation - failure - Error with pdf generation on report id: %d', $report->getId()));
        }

        return $pdf;
    }

    /**
     * @to-do move this into a checklist or pdf service
     * Generate the HTML of the report and convert to PDF
     *
     * @return string|false binary PDF content or false if PDF content is not available
     *
     * @throws Error
     */
    public function getChecklistPdfBinaryContent(Report $report): string|false
    {
        $reviewChecklist = $this->restClient->get('report/' . $report->getId() . '/checklist', 'Report\\ReviewChecklist');

        // A null id indicates a reviewChecklist has not yet been submitted.
        if ($reviewChecklist->getId() === null) {
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
        return $this->restClient->get("report-submission/$id", 'Report\\ReportSubmission');
    }

    public function getReportSubmissionsByIds(array $ids): array
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
     * @throws ReportSubmissionDocumentsNotDownloadableException
     */
    public function assertReportSubmissionIsDownloadable(ReportSubmission $reportSubmission)
    {
        if ($reportSubmission->isDownloadable() !== true) {
            throw new ReportSubmissionDocumentsNotDownloadableException(self::MSG_NOT_DOWNLOADABLE);
        }

        if (empty($reportSubmission->getDocuments())) {
            throw new ReportSubmissionDocumentsNotDownloadableException(self::MSG_NO_DOCUMENTS);
        }
    }
}
