<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\CourtOrder;

use OPG\Digideps\Backend\Entity\CourtOrder;

/**
 * Represents a pair of court orders, one hw and one pfa.
 * The orders are designated as main and sibling: both are required.
 * If the pair is valid (i.e. there are two court orders, one of each type);
 * then $invalidReason is null; otherwise the reason for their invalidity
 * is stored there.
 */
final class CourtOrderPair
{
    public function __construct(
        public CourtOrder $mainCourtOrder,
        public CourtOrder $siblingCourtOrder,
        public CourtOrder $pfaCourtOrder,
        public CourtOrder $hwCourtOrder,
    ) {
    }

    /**
     * Check that $courtOrders contains two court orders, one HW and one PFA;
     * NB these court orders don't have to be active at this point
     *
     * @throws \DomainException if the court orders are not a valid pfa/hw pair
     */
    public static function create(CourtOrder $mainCourtOrder, CourtOrder $siblingCourtOrder): CourtOrderPair
    {
        $courtOrderTypes = [];
        $pfaCourtOrder = $hwCourtOrder = null;

        foreach ([$mainCourtOrder, $siblingCourtOrder] as $courtOrder) {
            $orderType = $courtOrder->getOrderType();

            $courtOrderTypes[] = $orderType->value;

            if ($orderType === CourtOrderType::PFA) {
                $pfaCourtOrder = $courtOrder;
            } elseif ($orderType === CourtOrderType::HW) {
                $hwCourtOrder = $courtOrder;
            }
        }

        $expected = [CourtOrderType::HW->value, CourtOrderType::PFA->value];

        if ($pfaCourtOrder === null || $hwCourtOrder === null || count(array_diff($courtOrderTypes, $expected)) > 0) {
            throw new \DomainException(
                'Invalid pair of court orders: expected ' . implode(', ', $expected) .
                ' but types were ' . implode(', ', $courtOrderTypes)
            );
        }

        return new CourtOrderPair(
            mainCourtOrder: $mainCourtOrder,
            siblingCourtOrder: $siblingCourtOrder,
            pfaCourtOrder: $pfaCourtOrder,
            hwCourtOrder: $hwCourtOrder
        );
    }
}
