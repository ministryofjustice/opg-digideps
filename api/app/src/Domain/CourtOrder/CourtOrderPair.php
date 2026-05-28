<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\CourtOrder;

use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use phpDocumentor\Reflection\Types\Iterable_;

/**
 * Represents a pair of court orders, one hw and one pfa.
 * If the pair is valid (i.e. there are two court orders, one of each type);
 * then $invalidReason is null; otherwise the reason for their invalidity
 * is stored there.
 */
final class CourtOrderPair
{
    public function __construct(
        public ?CourtOrder $pfaCourtOrder = null {
        get {
        return $this->pfaCourtOrder;
        }
        },
        public ?CourtOrder $hwCourtOrder = null {
        get {
        return $this->hwCourtOrder;
        }
        },
        public ?string $invalidReason = null {
        get {
        return $this->invalidReason;
        }
        }
    ) {
    }

    /**
     * Check that $courtOrders contains two court orders, one HW and one PFA;
     * NB these court orders don't have to be active at this point
     *
     * @param iterable<?CourtOrder> $courtOrders
     */
    public static function create(iterable $courtOrders): CourtOrderPair
    {
        $courtOrderTypes = [];
        $pfaCourtOrder = $hwCourtOrder = null;

        foreach ($courtOrders as $courtOrder) {
            $orderType = $courtOrder?->getOrderType();
            if ($orderType === null) {
                continue;
            }

            $courtOrderTypes[] = $orderType->value;
            if ($orderType === CourtOrderType::PFA) {
                $pfaCourtOrder = $courtOrder;
            } elseif ($orderType === CourtOrderType::HW) {
                $hwCourtOrder = $courtOrder;
            }
        }

        $numCourtOrderTypes = count($courtOrderTypes);
        if ($numCourtOrderTypes !== 2) {
            return new CourtOrderPair(
                invalidReason: "Incorrect number of court orders: expected 2, but found $numCourtOrderTypes"
            );
        }

        $expected = [CourtOrderType::HW->value, CourtOrderType::PFA->value];

        if (count(array_diff($courtOrderTypes, $expected)) > 0) {
            return new CourtOrderPair(
                invalidReason: 'Invalid pair of court orders: expected ' . implode(', ', $expected) . ' but types were ' . implode(', ', $courtOrderTypes)
            );
        }

        // check pfa and hw court orders are set
        if ($hwCourtOrder === null || $pfaCourtOrder === null) {
            return new CourtOrderPair(invalidReason: 'Invalid pair of court orders: one or both is null');
        }

        return new CourtOrderPair(pfaCourtOrder: $pfaCourtOrder, hwCourtOrder: $hwCourtOrder, invalidReason: null);
    }

    public function isValid(): bool
    {
        return $this->invalidReason === null && $this->pfaCourtOrder !== null && $this->hwCourtOrder !== null;
    }
}
