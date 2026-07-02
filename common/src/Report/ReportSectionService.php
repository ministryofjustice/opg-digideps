<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report;

use OPG\Digideps\Common\Report\Section\ReportSection;
use OPG\Digideps\Common\Report\Section\SectionMetadata;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ReportSectionService
{
    public function __construct(
        private TranslatorInterface $translator
    ) {
    }

    public function getReportMetadata(ReportType $reportType): ReportMetadata
    {
        return new ReportMetadata($reportType, $this->translator);
    }

    public function getSectionMetadata(ReportType|ReportMetadata $reportTypeOrMetadata, ReportSection $section): SectionMetadata
    {
        return $this->upgrade($reportTypeOrMetadata)->getSectionMetadata($section);
    }

    /**
     * @return array<string>
     */
    public function getSectionIdsInReport(ReportType|ReportMetadata $reportTypeOrMetadata): array
    {
        return array_map(fn (ReportSection $section): string => $section->value, $this->upgrade($reportTypeOrMetadata)->getSectionsAsArray());
    }

    private function upgrade(ReportType|ReportMetadata $reportTypeOrMetadata): ReportMetadata
    {
        return $reportTypeOrMetadata instanceof ReportMetadata ? $reportTypeOrMetadata : $this->getReportMetadata($reportTypeOrMetadata);
    }
}
