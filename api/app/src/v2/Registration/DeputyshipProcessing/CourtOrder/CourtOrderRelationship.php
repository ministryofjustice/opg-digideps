<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\CourtOrder;

use App\Domain\CourtOrder\CourtOrderKind;

final readonly class CourtOrderRelationship
{
    public function __construct(public int $courtOrderId, public ?int $siblingId, public CourtOrderKind $kind)
    {
    }
}
