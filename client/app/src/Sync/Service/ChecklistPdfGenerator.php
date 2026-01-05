<?php

declare(strict_types=1);

namespace App\Sync\Service;

use App\Entity\Report\Report;
use App\Service\HtmlToPdfGenerator;
use App\Sync\Exception\PdfGenerationFailedException;
use Twig\Environment;

class ChecklistPdfGenerator
{
    public const string TEMPLATE_FILE = '@App/Admin/Client/Report/Formatted/checklist_formatted_standalone.html.twig';

    public function __construct(
        private readonly Environment $templating,
        private readonly HtmlToPdfGenerator $htmltopdf
    ) {
    }

    public function generate(Report $report): string
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
        } catch (\Throwable $e) {
            throw new PdfGenerationFailedException(sprintf('Unable to generate checklist PDF: %s: %s', $e->getCode(), $e->getMessage()));
        }
    }
}
