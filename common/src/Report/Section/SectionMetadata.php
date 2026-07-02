<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section;

use OPG\Digideps\Common\Report\ReportMetadata;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SectionMetadata
{
    public SectionTexts $texts;

    public function __construct(
        private ReportMetadata $reportMetadata,
        public ReportSection $section,
        private TranslatorInterface $translator,
    ) {
        $this->texts = new SectionTexts($this, $this->translator);
    }

    public function getLinkUrl(): string
    {
        return $this->translator->trans('');
    }

    public function getPreviousSection(): ?SectionMetadata
    {
        $previous = $this->reportMetadata->getSectionBefore($this->section);
        return $previous !== null ? $this->reportMetadata->getSectionMetadata($previous) : null;
    }

    public function getNextSection(): ?SectionMetadata
    {
        $next = $this->reportMetadata->getSectionAfter($this->section);
        return $next !== null ? $this->reportMetadata->getSectionMetadata($next) : null;
    }
}
