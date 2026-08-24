<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use OPG\Digideps\Backend\Cleanup\Model\Client;
use OPG\Digideps\Backend\Cleanup\Model\Report;
use OPG\Digideps\Backend\Entity\Cleanup\ReportCleanupAction;
use OPG\Digideps\Backend\Entity\Cleanup\ReportCleanupProblem;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class ClientInspector
{
    /**
     * @var array<ReportInspector> $reportInspectors
     */
    private array $reportInspectors;

    public function __construct(public Client $client)
    {
        $inspectors = [];
        foreach ($this->client->reports as $report) {
            $inspectors[] = new ReportInspector($report);
        }
        $this->reportInspectors = $inspectors;
    }

    /**
     * @return array<ReportCleanupAction|ReportCleanupProblem>
     */
    public function getCleaningActions(): array
    {
        $reports = array_map(fn (ReportInspector $inspector): Report => $inspector->report, $this->reportInspectors);
        $actions = [];
        foreach ($this->reportInspectors as $reportInspector) {
            if (!$reportInspector->isClean()) {
                $problems = $reportInspector->getCleaningProblems();
                if (!empty($problems)) {
                    $actions = array_merge($actions, $problems);
                } elseif ($reportInspector->hasNoOverlapWith(...$reports)) {
                    $actions = array_merge($actions, $reportInspector->getCleaningActions());
                } else {
                    $actions = array_merge($actions, $reportInspector->getCleaningActions(true));
                }
            }
        }
        return $actions;
    }
}
