<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use OPG\Digideps\Backend\Cleanup\Model\Order;
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

    public function hasNoOverlapWith(Report ...$reports): bool
    {
        return empty(array_filter($reports, fn (Report $report): bool => $this->overlapsWithOtherReport($report)));
    }

    private function hasNoCourtOrder(): bool
    {
        return empty($this->report->orders);
    }

    private function hasMadeDateBeforeStartOrEndDate(): bool
    {
        if ($this->hasNoCourtOrder()) {
            return false;
        }
        $madeDate = $this->report->orders[0]->madeDate;
        foreach ($this->report->orders as $order) {
            if ($order->madeDate < $madeDate) {
                $madeDate = $order->madeDate;
            }
        }
        return $madeDate <= $this->report->endDate || $madeDate <= $this->report->startDate;
    }

    private function hasMultipleActiveOrders(): bool
    {
        return count(array_filter($this->report->orders, fn (Order $order) => $order->open)) > 1;
    }

    private function overlapsWithOtherReport(Report $report): bool
    {
        return $report->reportId !== $this->report->reportId
            && $report->endDate >= $this->report->startDate
            && $report->startDate <= $this->report->endDate;
    }

    /**
     * @return array<ReportCleanupProblem>
     */
    public function getCleaningProblems(): array
    {
        $problems = [];

        if ($this->hasNoCourtOrder()) {
            return [new ReportCleanupProblem(
                $this->report->client->clientId,
                $this->report->reportId,
                Problem::NoOrders
            )];
        }
        if (!$this->hasMadeDateBeforeStartOrEndDate()) {
            $problems[] = new ReportCleanupProblem(
                $this->report->client->clientId,
                $this->report->reportId,
                Problem::BeforeOrders
            );
        }
        if ($this->hasMultipleActiveOrders()) {
            $problems[] = new ReportCleanupProblem(
                $this->report->client->clientId,
                $this->report->reportId,
                Problem::MultipleActiveOrders
            );
        }

        return $problems;
    }

    /**
     * @return array<ReportCleanupAction|ReportCleanupProblem>
     */
    public function getCleaningActions(bool $useEndDateNotStartDate = false): array
    {
        $actions = [];

        $candidateOrders = array_filter(
            $this->report->orders,
            fn (Order $order) => $order->madeDate <= ($useEndDateNotStartDate ? $this->report->endDate : $this->report->startDate)
        );
        $activeOrderMadeDate = $this->report->client->activeOrder?->madeDate;

        //Ideally we ignore orders after the active order
        $priorityCandidateOrders = $activeOrderMadeDate === null ? $candidateOrders : array_filter(
            $candidateOrders,
            fn (Order $order) => $order->madeDate <= $activeOrderMadeDate
        );

        $keptOrder = null;
        foreach ($priorityCandidateOrders as $order) {
            $keptOrder ??= $order;
            if ($order->madeDate > $keptOrder->madeDate) {
                $keptOrder = $order;
            }
        }
        //But the report might not have been linked to any of these
        if ($keptOrder === null) {
            foreach ($candidateOrders as $order) {
                $keptOrder ??= $order;
                if ($order->madeDate > $keptOrder->madeDate) {
                    $keptOrder = $order;
                }
            }
        }
        //Existing links are utterly nonsense; but we must keep one since we will not add new links
        if ($keptOrder === null) {
            foreach ($this->report->orders as $order) {
                $keptOrder ??= $order;
                if ($order->madeDate > $keptOrder->madeDate) {
                    $keptOrder = $order;
                }
            }
        }

        if ($keptOrder === null) {
            return []; //Should never be hit since there are at least two orders if this function is called correctly
        }

        foreach ($this->report->orders as $order) {
            $actions[] = new ReportCleanupAction($this->report->reportId, $order->orderId, $order->orderId === $keptOrder->orderId);
        }

        return $actions;
    }
}
