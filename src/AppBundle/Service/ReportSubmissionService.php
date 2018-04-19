<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\File\Storage\S3Storage;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Psr\Log\LoggerInterface;

class ReportSubmissionService
{

    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DocumentService constructor.
     * @param S3Storage       $s3Storage
     * @param RestClient      $restClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        FileUploader $fileUploader,
        RestClient $restClient,
        LoggerInterface $logger)
    {
        $this->fileUploader = $fileUploader;
        $this->restClient = $restClient;
        $this->logger = $logger;
    }

    public function generateReportDocuments(Report $report)
    {
        $this->generateReportPdf($report);
        //$this->generateTransactionsCsv($report);
    }

    private function generateReportPdf(Report $report)
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
     * @param  Report $report
     * @param  bool                    $showSummary
     * @return string                  binary PDF content
     */
    private function getPdfBinaryContent(Report $report, $showSummary = false)
    {
        $html = $this->render('AppBundle:Report/Formatted:formatted_body.html.twig', [
            'report' => $report,
            'showSummary' => $showSummary
        ])->getContent();

        return $this->get('wkhtmltopdf')->getPdfFromHtml($html);
    }
}
