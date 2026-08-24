<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup\Model;

final readonly class Report
{
    /**
     * @var array<Order> $orders
     */
    public array $orders;

    public function __construct(
        public Client $client,
        public int $reportId,
        public \DateTimeImmutable $startDate,
        public \DateTimeImmutable $endDate,
        public bool $submitted,
        Order ...$orders,
    ) {
        usort($orders, fn (Order $left, Order $right) => $left->madeDate->getTimestamp() <=> $right->madeDate->getTimestamp());
        $this->orders = $orders;
    }
}
