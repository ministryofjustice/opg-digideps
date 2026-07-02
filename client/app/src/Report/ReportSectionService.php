<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Report;

use OPG\Digideps\Common\Report\ReportMetadata;
use OPG\Digideps\Common\Report\ReportType;
use OPG\Digideps\Common\Report\Section\Link\SectionLink;
use OPG\Digideps\Common\Report\Section\ReportSection;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Report\Section\SectionMetadata;
use OPG\Digideps\Frontend\Report\Section\SectionTexts;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ReportSectionService
{
    public function __construct(
        private TranslatorInterface $translator,
        private RouterInterface $router,
    ) {
    }

    public function getReportMetadata(Report $report): ReportMetadata
    {
        return new ReportMetadata($report->getId(), ReportType::from($report->getType()));
    }

    public function getSectionMetadata(Report|ReportMetadata $reportOrMetadata, ReportSection $section, array $parameters = []): ?SectionMetadata
    {
        $metadata = $reportOrMetadata instanceof Report ? $this->getReportMetadata($reportOrMetadata) : $reportOrMetadata;
        $section = $metadata->getSectionLike($section);
        return $section !== null ? new SectionMetadata(
            new SectionTexts($section, $metadata, $this->translator, $parameters),
            $this->resolveLink($metadata->getOverviewLink()),
            $this->resolveLink($metadata->getSectionLink($section)),
            $this->resolveLink($metadata->getSectionBeforeLink($section)),
            $this->resolveLink($metadata->getSectionAfterLink($section)),
        ) : null;
    }

    private function resolveLink(SectionLink $link): string
    {
        return $this->router->generate($link->url->name, $link->url->parameters);
    }
}
