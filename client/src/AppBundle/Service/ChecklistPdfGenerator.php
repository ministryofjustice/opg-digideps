<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Exception\PdfGenerationFailedException;
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

    const TEMPLATE_FILE = 'AppBundle:Admin/Client/Report/Formatted:checklist_formatted_standalone.html.twig';

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

    /**
     * @param Report $report
     * @return string
     */
    public function generate(Report $report)
    {
        try {
            $html = $this->templating->render(self::TEMPLATE_FILE, [
                'report' => $report,
                'lodgingChecklist' => $report->getChecklist(),
                'reviewChecklist' => $report->getReviewChecklist()
            ]);

            if (false === ($pdf = $this->wkhtmltopdf->getPdfFromHtml($html))) {
                throw new PdfGenerationFailedException('Unable to generate PDF using wkhtmltopdf service');
            }

            return $pdf;
        } catch (Throwable $e) {
            throw new PdfGenerationFailedException(sprintf('Unable to generate checklist PDF: %s: %s', $e->getCode(), $e->getMessage()));
        }
    }
}
