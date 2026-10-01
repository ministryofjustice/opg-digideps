<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Review;

use OPG\Digideps\Common\Report\Section\ReportSection;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use OPG\Digideps\Frontend\Entity\Report\Report;

final readonly class Section implements RenderableInterface
{
    public string $componentName;
    public array $props;

    public function __construct(Report $report, ReportSection $reportSection)
    {
        $this->componentName = $this->calculateComponentName($reportSection);
        $this->props = ['report' => $report];
    }

    private function calculateComponentName(ReportSection $reportSection): string
    {
        $name = $reportSection->value;
        $name[0] = mb_strtoupper($name[0]);
        return "OPG:Review:{$name}";
    }
}
