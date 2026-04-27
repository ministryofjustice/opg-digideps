<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;

final readonly class CourtOrderRelationship
{
    public function __construct(
        public int $clientId,
        public int $courtOrderId,
        public ?int $siblingId,
        public CourtOrderKind $kind
    ) {
    }
}
