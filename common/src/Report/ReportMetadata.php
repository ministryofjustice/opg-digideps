<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report;

use OPG\Digideps\Common\Report\Section\Link\RoutedUrl;
use OPG\Digideps\Common\Report\Section\Link\SectionLink;
use OPG\Digideps\Common\Report\Section\Link\TranslatedText;
use OPG\Digideps\Common\Report\Section\ReportSection;
use OPG\Digideps\Common\Report\Section\Sections;

final readonly class ReportMetadata
{
    public Sections $sections;

    public function __construct(
        public int $reportId,
        public ReportType $reportType,
    ) {
        $this->sections = Sections::new($this->reportType);
    }

    public function hasSection(?ReportSection $section): bool
    {
        return $section !== null && $this->sections->hasSection($section);
    }

    public function getSectionAfter(?ReportSection $section): ?ReportSection
    {
        return $section !== null ? $this->sections->getSectionAfter($section) : null;
    }

    public function getSectionBefore(?ReportSection $section): ?ReportSection
    {
        return $section !== null ? $this->sections->getSectionBefore($section) : null;
    }

    public function getSectionLike(?ReportSection $section): ?ReportSection
    {
        return match ($section) {
            ReportSection::DECISIONS, ReportSection::BALANCE, ReportSection::DOCUMENTS, ReportSection::OTHER_INFO, ReportSection::ACTIONS, ReportSection::PROF_DEPUTY_COSTS_ESTIMATE, ReportSection::DEBTS, ReportSection::ASSETS, ReportSection::MONEY_TRANSFERS, ReportSection::GIFTS, ReportSection::BANK_ACCOUNTS, ReportSection::CLIENT_BENEFITS_CHECK, ReportSection::LIFESTYLE, ReportSection::VISITS_CARE, ReportSection::CONTACTS => $this->filterSection($section),
            ReportSection::MONEY_IN, ReportSection::MONEY_IN_SHORT => $this->filterSection(ReportSection::MONEY_IN_SHORT, ReportSection::MONEY_IN),
            ReportSection::MONEY_OUT, ReportSection::MONEY_OUT_SHORT => $this->filterSection(ReportSection::MONEY_OUT_SHORT, ReportSection::MONEY_OUT),
            ReportSection::DEPUTY_EXPENSES, ReportSection::PROF_DEPUTY_COSTS, ReportSection::PA_DEPUTY_EXPENSES => $this->filterSection(ReportSection::DEPUTY_EXPENSES, ReportSection::PROF_DEPUTY_COSTS, ReportSection::PA_DEPUTY_EXPENSES),
            default => null,
        };
    }

    public function getSectionLink(?ReportSection $section, bool $like = false): SectionLink
    {
        $section = $like ? $this->getSectionLike($section) : $this->filterSection($section);
        return $section !== null ? new SectionLink(
            new RoutedUrl(RoutedUrl::snakeCase($section->value), ['reportId' => $this->reportId]),
            new TranslatedText('report-sections', "title.{$section->value}")
        ) : $this->getOverviewLink();
    }

    public function getSectionBeforeLink(?ReportSection $section, bool $like = false): SectionLink
    {
        $section = $this->getSectionBefore($like ? $this->getSectionLike($section) : $this->filterSection($section));
        return $this->getSectionLink($section);
    }

    public function getSectionAfterLink(?ReportSection $section, bool $like = false): SectionLink
    {
        $section = $this->getSectionAfter($like ? $this->getSectionLike($section) : $this->filterSection($section));
        return $this->getSectionLink($section);
    }

    public function getOverviewLink(): SectionLink
    {
        return new SectionLink(
            new RoutedUrl('report_overview', ['reportId' => $this->reportId]),
            new TranslatedText('report-sections', 'common.overviewLink')
        );
    }

    private function filterSection(?ReportSection ...$sections): ?ReportSection
    {
        return array_find($sections, fn ($section) => $this->hasSection($section));
    }
}
