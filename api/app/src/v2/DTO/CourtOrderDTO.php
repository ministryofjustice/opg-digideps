<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\DTO;

use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;

class CourtOrderDTO
{
    public function __construct(
        public readonly int $id,
        public string $courtOrderUid,
        public CourtOrderType $orderType,
        public CourtOrderReportType $orderReportType,
        public string $status,
        public \DateTime $orderMadeDate,
    ) {
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'courtOrderUid' => $this->courtOrderUid,
            'orderType' => $this->orderType->value,
            'orderReportType' => $this->orderReportType->value,
            'status' => $this->status,
            'orderMadeDate' => $this->orderMadeDate->format('Y-m-d'),
        ];
    }
}
