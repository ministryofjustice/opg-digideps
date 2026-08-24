<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use OPG\Digideps\Backend\Cleanup\Model\Problem;
use OPG\Digideps\Backend\Cleanup\Model\Report;
use OPG\Digideps\Backend\Entity\Cleanup\ReportCleanupAction;
use OPG\Digideps\Backend\Entity\Cleanup\ReportCleanupProblem;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class ReportInspector
{
    public function __construct(public Report $report)
    {
    }

    public function isClean(): bool
    {
        return count($this->report->orders) === 1;
    }

    public function hasManyCourtOrder(): bool
    {
        return count($this->report->orders) > 1;
    }

    public function hasNoOverlapWith(Report ...$reports): bool
    {
        return empty(array_filter($reports, fn (Report $report): bool => $this->overlapsWithOtherReport($report)));
    }

    public function hasNoCourtOrder(): bool
    {
        return empty($this->report->orders);
    }

    public function hasMadeDateBeforeStartOrEndDate(): bool
    {
        if ($this->hasNoCourtOrder()) {
            return false;
        }
        $madeDate = $this->report->orders[count($this->report->orders) - 1]->madeDate->getTimestamp();
        return $madeDate <= $this->report->endDate->getTimestamp() || $madeDate <= $this->report->startDate->getTimestamp();
    }

    public function isWellOrdered(): bool
    {
        $active = $this->report->orders[0]->open;
        foreach ($this->report->orders as $order) {
            if ($active) {
                if (!$order->open) {
                    $active = false;
                }
            } else {
                if ($order->open) {
                    return false;
                }
            }
        }
        return true;
    }

    private function overlapsWithOtherReport(Report $report): bool
    {
        return $report->reportId !== $this->report->reportId
            && $report->endDate->getTimestamp() >= $this->report->startDate->getTimestamp()
            && $report->startDate->getTimestamp() <= $this->report->endDate->getTimestamp();
    }

    /**
     * @return array<ReportCleanupAction|ReportCleanupProblem>
     */
    public function getCleaningActions(): array
    {
        if ($this->isClean()) {
            return [];
        } elseif ($this->hasNoCourtOrder()) {
            return [new ReportCleanupProblem(
                $this->report->client->clientId,
                $this->report->reportId,
                null,
                Problem::NoOrders
            )];
        }

        $actions = [];

        if (!$this->hasMadeDateBeforeStartOrEndDate()) {
            $actions[] = new ReportCleanupProblem(
                $this->report->client->clientId,
                $this->report->reportId,
                null,
                Problem::BeforeOrders
            );
        }
        if (!$this->isWellOrdered()) {
            $actions[] = new ReportCleanupProblem(
                $this->report->client->clientId,
                $this->report->reportId,
                null,
                Problem::MessyOrders
            );
        }

        if (empty($actions)) {
            $keptOrder = null;
            foreach ($this->report->orders as $order) {
                if ($order->madeDate->getTimestamp() <= $this->report->startDate->getTimestamp()) {
                    $keptOrder = $order;
                    break;
                }
            }
            $keptOrder ??= $this->report->orders[count($this->report->orders) - 1];
            foreach ($this->report->orders as $order) {
                $actions[] = new ReportCleanupAction($this->report->reportId, $order->orderId, $order->orderId === $keptOrder->orderId);
            }
        }

        return $actions;
    }
}
