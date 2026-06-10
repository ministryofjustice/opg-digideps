<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\CourtOrder;

use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;

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
        if ($mainCourtOrder->getOrderType() === $siblingCourtOrder->getOrderType()) {
            throw new \DomainException(
                "Invalid pair of court orders (both have type {$mainCourtOrder->getOrderType()->value})"
            );
        }

        [$pfaCourtOrder, $hwCourtOrder] = match ($mainCourtOrder->getOrderType()) {
            CourtOrderType::PFA => [$mainCourtOrder, $siblingCourtOrder],
            CourtOrderType::HW => [$siblingCourtOrder, $mainCourtOrder],
        };

        return new CourtOrderPair(
            mainCourtOrder: $mainCourtOrder,
            siblingCourtOrder: $siblingCourtOrder,
            pfaCourtOrder: $pfaCourtOrder,
            hwCourtOrder: $hwCourtOrder
        );
    }
}
