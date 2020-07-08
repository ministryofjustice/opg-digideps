<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use Psr\Log\LoggerInterface;
use Throwable;
use Twig\Environment;

class ChecklistPdfGenerator
{
    /** @var Environment container */
    private $templating;

    /** @var WkHtmlToPdfGenerator */
    private $wkhtmltopdf;

    /** @var LoggerInterface */
    private $logger;

    /** @var int */
    const FAILED_TO_GENERATE = -1;

    /**
     * @param Environment $templating
     * @param WkHtmlToPdfGenerator $wkhtmltopdf
     * @param LoggerInterface $logger
     */
    public function __construct(Environment $templating, WkHtmlToPdfGenerator $wkhtmltopdf, LoggerInterface $logger)
    {
        $this->templating = $templating;
        $this->wkhtmltopdf = $wkhtmltopdf;
        $this->logger = $logger;
    }

    /***
     * @param Report $report
     * @return int|string
     */
    public function generate(Report $report)
    {
        try {
            $html = $this->templating->render('AppBundle:Admin/Client/Report/Formatted:checklist_formatted_standalone.html.twig', [
                'report' => $report,
                'lodgingChecklist' => $report->getChecklist(),
                'reviewChecklist' => $report->getReviewChecklist()
            ]);

            return $this->wkhtmltopdf->getPdfFromHtml($html);
        } catch (Throwable $e) {
            // Repeat occurrences will cause an alert triggered by Cloudwatch.
            $this->logger->critical(sprintf('Unable to generate checklist PDF: %s: %s', $e->getCode(), $e->getMessage()));
            return self::FAILED_TO_GENERATE;
        }
    }
}
