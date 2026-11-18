<?php

namespace OPG\Digideps\Frontend\Service;

use OPG\Digideps\Common\Report\Section\Link\RoutedUrl;
use OPG\Digideps\Common\Report\Section\ReportSection;
use OPG\Digideps\Frontend\Entity\Report\Report;
use Symfony\Component\Routing\RouterInterface;

class ReportSectionsMetaService
{
    protected RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return array<array{
     *     link: string,
     *     needsAttention: bool,
     *     section: string,
     *     status: string
     * }>
     */
    private function getOptions(Report $report): array
    {
        $status = $report->getStatus();
        $routeParams = ['reportId' => $report->getId()];

        $config = [];

        // cycle order and add config for each one
        foreach ($report->getAvailableSections() as $sectionId) {
            if ($section = ReportSection::tryFrom($sectionId)) {
                $routeName = RoutedUrl::snakeCase($section->getValueForRouting());

                $sectionStateFunction = 'get' . ucfirst($section->value) . 'State';
                /** @var String[] $sectionState */
                $sectionState = $status->$sectionStateFunction();

                $config[] = [
                    'link' => $this->router->generate($routeName, $routeParams),
                    'needsAttention' => $report->isSectionFlaggedForAttention($section->value),
                    'section' => $section->value,
                    'status' => $sectionState['state'],
                ];
            }
        }

        return $config;
    }

    /**
     * @return array empty if it's the last or first section
     */
    public function getSectionParams(Report $report, string $sectionId, int $offset = 0): array
    {
        $config = $this->getOptions($report);

        foreach ($config as $index => $currentSectionParams) {
            if ($currentSectionParams['section'] == $sectionId) {
                return $config[$index + $offset] ?? [];
            }
        }

        return [];
    }
}
