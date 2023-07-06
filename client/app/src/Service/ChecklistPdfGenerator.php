<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report\Report;
use App\Exception\PdfGenerationFailedException;
use Psr\Log\LoggerInterface;
use Throwable;
use Twig\Environment;

class ChecklistPdfGenerator
{
    /** @var Environment container */
    private $templating;

    /** @var HtmlToPdfGenerator */
    private $htmltopdf;

    /** @var LoggerInterface */
    private $logger;

    public const TEMPLATE_FILE = '@App/Admin/Client/Report/Formatted/checklist_formatted_standalone.html.twig';

    public function __construct(Environment $templating, HtmlToPdfGenerator $htmltopdf, LoggerInterface $logger)
    {
        $this->templating = $templating;
        $this->htmltopdf = $htmltopdf;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function generate(Report $report)
    {
        try {
            $html = $this->templating->render(self::TEMPLATE_FILE, [
                'report' => $report,
                'lodgingChecklist' => $report->getChecklist(),
                'reviewChecklist' => $report->getReviewChecklist(),
            ]);

            if (false === ($pdf = $this->htmltopdf->getPdfFromHtml($html))) {
                throw new PdfGenerationFailedException('Unable to generate PDF using htmltopdf service');
            }

            return $pdf;
        } catch (Throwable $e) {
            throw new PdfGenerationFailedException(sprintf('Unable to generate checklist PDF: %s: %s', $e->getCode(), $e->getMessage()));
        }
    }
}
