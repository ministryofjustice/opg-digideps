<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report;

use OPG\Digideps\Common\Report\Section\ReportSection;
use OPG\Digideps\Common\Report\Section\SectionMetadata;
use OPG\Digideps\Common\Report\Section\Sections;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ReportMetadata
{
    private Sections $sections;

    public function __construct(
        public ReportType $reportType,
        private TranslatorInterface $translator,
    ) {
        $this->sections = Sections::new($this->reportType);
    }

    /**
     * @return \Traversable<int, ReportSection>
     */
    public function getSections(): \Traversable
    {
        return $this->sections->getIterator();
    }

    /**
     * @return array<ReportSection>
     */
    public function getSectionsAsArray(): array
    {
        return [...$this->sections->getIterator()];
    }

    public function getSectionMetadata(ReportSection $section): SectionMetadata
    {
        return new SectionMetadata($this, $section, $this->translator);
    }

    public function hasSection(ReportSection $section): bool
    {
        return $this->sections->hasSection($section);
    }

    public function getSectionAfter(ReportSection $section): ?ReportSection
    {
        return $this->sections->getSectionAfter($section);
    }

    public function getSectionBefore(ReportSection $section): ?ReportSection
    {
        return $this->sections->getSectionBefore($section);
    }
}
