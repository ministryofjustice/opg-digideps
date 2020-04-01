<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use AppBundle\Exception\ReportSubmissionDocumentsNotDownloadableException;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSenderInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class ReportSubmissionService
{
    const MSG_NOT_DOWNLOADABLE = 'This report is not downloadable';
    const MSG_NO_DOCUMENTS = 'No documents found for downloading';

    /**
     * @var FileUploader
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
     * @var WkHtmlToPdfGenerator
     */
    private $wkhtmltopdf;

    /**
     * @var MailSenderInterface
     */
    private $mailSender;

    /**
     * @var MailFactory
     */
    private $mailFactory;

    /**
     * @var CsvGeneratorService
     */
    private $csvGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ReportSubmissionService constructor.
     * @throws \Exception
     */
    public function __construct(
        CsvGeneratorService $csvGenerator,
        Environment $templating,
        FileUploader $fileUploader,
        RestClient $restClient,
        LoggerInterface $logger,
        MailFactory $mailFactory,
        MailSenderInterface $mailSender,
        WkHtmlToPdfGenerator $wkhtmltopdf
    )
    {
        $this->fileUploader = $fileUploader;
        $this->restClient = $restClient;
        $this->mailSender = $mailSender;
        $this->mailFactory =$mailFactory;
        $this->templating = $templating;
        $this->wkhtmltopdf = $wkhtmltopdf;
        $this->logger = $logger;
        $this->csvGenerator = $csvGenerator;
    }

    /**
     * Wrapper method for all documents generated for a report submission
     * @param Report $report
     */
    public function generateReportDocuments(ReportInterface $report)
    {
        $this->generateReportPdf($report);
        $csvContent = $this->csvGenerator->generateTransactionsCsv($report);

        $this->fileUploader->uploadFile(
            $report,
            $csvContent,
            $report->createAttachmentName('DigiRepTransactions-%s_%s_%s.csv'),
            true
        );
    }

    /**
     * Generates the PDF of the report
     *
     * @param Report $report
     */
    private function generateReportPdf(ReportInterface $report)
    {
        // store PDF (with summary info) as a document
        $this->fileUploader->uploadFile(
            $report,
            $this->getPdfBinaryContent($report, true),
            $report->createAttachmentName('DigiRep-%s_%s_%s.pdf'),
            true
        );
    }

    /**
     * Generate the HTML of the report and convert to PDF
     *
     * @param  Report $report
     * @param  bool   $showSummary
     * @return string binary PDF content
     */
    public function getPdfBinaryContent(ReportInterface $report, $showSummary = false)
    {
        $html = $this->templating->render('AppBundle:Report/Formatted:formatted_standalone.html.twig', [
            'report' => $report,
            'showSummary' => $showSummary
        ]);

        return $this->wkhtmltopdf->getPdfFromHtml($html);
    }

    /**
     * @to-do move this into a checklist or pdf service
     * Generate the HTML of the report and convert to PDF
     *
     * @param  Report $report
     * @param  bool   $showSummary
     * @return string binary PDF content
     */
    public function getChecklistPdfBinaryContent(ReportInterface $report)
    {
        $reviewChecklist = $this->restClient->get('report/' . $report->getId() . '/checklist', 'Report\\ReviewChecklist');

        // A null id indicates a reviewChecklist has not yet been submitted.
        if (null === $reviewChecklist->getId()) {
            $reviewChecklist = null;
        }

        $html = $this->templating->render('AppBundle:Admin/Client/Report/Formatted:checklist_formatted_standalone.html.twig', [
            'report' => $report,
            'lodgingChecklist' => $report->getChecklist(),
            'reviewChecklist' => $reviewChecklist
        ]);

        return $this->wkhtmltopdf->getPdfFromHtml($html);
    }

    /**
     * @param Report $report
     * @param User   $user
     */
    public function submit(ReportInterface $report, User $user)
    {
        // store report and get new YEAR report (only for reports submitted the first time)
        $newYearReportId = $this->restClient->put('report/' . $report->getId() . '/submit', $report, ['submit']);
        if ($newYearReportId) {
            $newReport = $this->restClient->get('report/' . $newYearReportId, 'Report\\Report');

            $reportConfirmEmail = $this->mailFactory->createReportSubmissionConfirmationEmail($user, $report, $newReport);
            $this->mailSender->send($reportConfirmEmail);
        }
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function getReportSubmissionById(string $id)
    {
        return $this->restClient->get( "report-submission/${id}", 'Report\\ReportSubmission');
    }

    /**
     * @param array $ids
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
     * @param ReportSubmission $reportSubmission
     * @throws ReportSubmissionDocumentsNotDownloadableExceptionAlias
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
