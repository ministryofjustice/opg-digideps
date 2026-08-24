<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup\Model;

final readonly class Client
{
    /**
     * @var array<Report>
     */
    public array $reports;
    /**
     * @var array<Order>
     */
    public array $orders;

    /**
     * @param array<array<string, mixed>> $rows
     */
    public function __construct(
        public int $clientId,
        public string $caseNumber,
        array $rows,
    ) {
        $orders = [];
        $reports = [];
        foreach ($rows as $row) {
            $orderId = (int)$row['order_id'];
            $reportId = (int)$row['report_id'];
            if (!array_key_exists($orderId, $orders)) {
                $orders[$orderId] = new Order($this, $orderId, $row['order_uid'], new \DateTimeImmutable($row['order_made_date'])->setTime(0, 0), (bool)$row['open']);
            }
            $reports[$reportId] ??= [
                'orders' => [],
                'report_id' => $reportId,
                'start_date' => new \DateTimeImmutable($row['report_start_date'])->setTime(0, 0),
                'end_date' => new \DateTimeImmutable($row['report_end_date'])->setTime(0, 0),
                'submitted' => $row['submitted']
            ];
            $reports[$reportId]['orders'][$orderId] = $orders[$orderId];
        }
        usort($orders, fn (Order $left, Order $right) => $left->madeDate->getTimestamp() <=> $right->madeDate->getTimestamp());
        $this->orders = $orders;
        $reports = array_map(fn (array $data): Report => new Report(
            $this,
            $data['report_id'],
            $data['start_date'],
            $data['end_date'],
            $data['submitted'],
            ...$data['orders']
        ), $reports);
        usort($reports, fn (Report $left, Report $right) => $left->startDate->getTimestamp() <=> $right->endDate->getTimestamp());
        $this->reports = $reports;
    }
}
