<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\CourtOrder;

class CourtOrderFactory
{
    public static function create(
        ?string $orderUid,
        ?string $orderType,
        ?string $status,
        ?string $orderMadeDate,
    ): ?CourtOrder {
        // validation (of sorts)
        if (in_array(null, [$orderUid, $orderType, $status, $orderMadeDate])) {
            return null;
        }

        $courtOrder = new CourtOrder();
        $courtOrder->setCourtOrderUid($orderUid);
        $courtOrder->setOrderType($orderType);
        $courtOrder->setStatus($status);
        $courtOrder->setOrderMadeDate(new \DateTime($orderMadeDate));

        return $courtOrder;
    }
}
