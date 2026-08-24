<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class ReportInspector
{
    /**
     * @var array<CourtOrder> $orders
     */
    private array $orders;

    public function __construct(public Report $report)
    {
        $orders = $this->report->getCourtOrders()->toArray();
        usort($orders, fn (CourtOrder $left, CourtOrder $right) => -$left->getOrderMadeDate()->getTimestamp() <=> -$right->getOrderMadeDate()->getTimestamp());
        $this->orders = $orders;
    }

    public function isClean(): bool
    {
        if (count($this->orders) > 2) {
            return false;
        } elseif (count($this->orders) === 2) {
            if (!$this->report->isHybrid() || count(array_filter($this->orders, fn (CourtOrder $order) => $order->getOrderType() === CourtOrderType::PFA)) !== 1) {
                return false;
            }
        }
        return true;
    }

    public function hasNoOverlapWith(Report ...$reports): bool
    {
        return empty(array_filter($reports, fn (Report $report): bool => $this->overlapsWithOtherReport($report)));
    }

    public function hasNoCourtOrder(): bool
    {
        return empty($this->orders);
    }

    public function hasMadeDateBeforeStartOrEndDate(): bool
    {
        if ($this->hasNoCourtOrder()) {
            return false;
        }
        $madeDate = $this->orders[count($this->orders) - 1]->getOrderMadeDate()->getTimestamp();
        return $madeDate <= $this->report->getEndDate()->getTimestamp() || $madeDate <= $this->report->getStartDate()->getTimestamp();
    }

    public function isWellOrdered(): bool
    {
        $active = $this->orders[0]->getStatus() === 'ACTIVE';
        foreach ($this->orders as $order) {
            if ($active) {
                if ($order->getStatus() !== 'ACTIVE') {
                    $active = false;
                }
            } else {
                if ($order->getStatus() === 'ACTIVE') {
                    return false;
                }
            }
        }
        return true;
    }

    private function overlapsWithOtherReport(Report $report): bool
    {
        return $report->getId() !== $this->report->getId()
            && $report->getEndDate()->getTimestamp() >= $this->report->getStartDate()->getTimestamp()
            && $report->getStartDate()->getTimestamp() <= $this->report->getEndDate()->getTimestamp();
    }

    /**
     * @return array<CleanupPlan|CleanupProblem>
     */
    public function getCleaningActions(): array
    {
        if ($this->isClean()) {
            return [];
        }


        if ($this->hasNoCourtOrder()) {
            return [new CleanupProblem($this->report->getClient(), $this->report, 'No court orders')];
        }

        $actions = [];

        if (!$this->hasMadeDateBeforeStartOrEndDate()) {
            $actions[] = new CleanupProblem($this->report->getClient(), $this->report, 'No order made date equal or smaller than either report end or start date');
        }

        if (!$this->isWellOrdered()) {
            $actions[] = new CleanupProblem($this->report->getClient(), $this->report, 'Some inactive court orders have a made date more recent than that of active orders');
        }

        if (empty($actions)) {
            $keptOrder = null;
            foreach ($this->orders as $order) {
                if ($order->getOrderMadeDate()->getTimestamp() <= $this->report->getStartDate()->getTimestamp()) {
                    $keptOrder = $order;
                    break;
                }
            }
            $keptOrder ??= $this->orders[count($this->orders) - 1];
            foreach ($this->orders as $order) {
                $actions[] = new CleanupPlan($order, $this->report, $order->getId() === $keptOrder->getId());
            }
        }

        return $actions;
    }
}
