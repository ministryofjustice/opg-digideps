<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\CourtOrder;

class CourtOrderDto
{
    public function __construct(
        public readonly int $id,
        public string $courtOrderUid,
        public ?CourtOrderType $orderType,
        public ?CourtOrderReportType $orderReportType,
        public string $status,
        public \DateTime $orderMadeDate,
    ) {
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'court_order_uid' => $this->courtOrderUid,
            'order_type' => $this->orderType->value ?? '',
            'order_report_type' => $this->orderReportType->value ?? '',
            'status' => $this->status,
            'order_made_date' => $this->orderMadeDate->format('Y-m-d'),
        ];
    }
}
