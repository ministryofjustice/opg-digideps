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

    public function isClean(): bool
    {
        return array_all($this->reportInspectors, fn (ReportInspector $inspector): bool => $inspector->isClean());
    }

    public function isContinuous(): bool
    {
        $reports = array_map(fn (ReportInspector $inspector): Report => $inspector->report, $this->reportInspectors);
        return array_all($this->reportInspectors, fn (ReportInspector $inspector): bool => $inspector->hasNoOverlapWith(...$reports));
    }

    /**
     * @return array<ReportCleanupAction|ReportCleanupProblem>
     */
    public function getCleaningActions(): array
    {
        return array_merge(...array_map(fn (ReportInspector $inspector): array => $inspector->getCleaningActions(), $this->reportInspectors));
    }
}
