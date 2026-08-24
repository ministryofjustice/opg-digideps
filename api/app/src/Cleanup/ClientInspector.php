<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class ClientInspector
{
    /**
     * @var array<CourtOrder> $orders
     */
    private array $orders;
    /**
     * @var array<ReportInspector> $reportInspectors
     */
    private array $reportInspectors;
    /**
     * @var array<Report> $insaneReports;
     */
    public array $insaneReports;

    public function __construct(public Client $client)
    {
        $this->orders = $this->client->getCourtOrders()->toArray();
        $reports = [];
        $insaneReports = [];
        foreach ($this->orders as $order) {
            foreach ($order->getReports() as $report) {
                if ($report->getClient()->getId() !== $this->client->getId()) {
                    $insaneReports[$report->getId()] = $report;
                } else {
                    $reports[$report->getId()] = $report;
                }
            }
        }
        $this->reportInspectors = array_map(fn (Report $report) => new ReportInspector($report), array_values($reports));
        $this->insaneReports = $insaneReports;
    }

    public function isSane(): bool
    {
        return empty($this->insaneReports);
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
     * @return array<CleanupPlan|CleanupProblem>
     */
    public function getCleaningActions(): array
    {
        return array_merge(...array_map(fn (ReportInspector $inspector): array => $inspector->getCleaningActions(), $this->reportInspectors));
    }
}
