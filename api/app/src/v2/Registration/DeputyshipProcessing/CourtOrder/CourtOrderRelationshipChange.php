<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\CourtOrder;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Entity\CourtOrder;

final readonly class CourtOrderRelationshipChange
{
    public function __construct(public CourtOrder $courtOrder, public ?CourtOrderKind $oldKind, public ?int $oldSiblingId)
    {
    }

    public function hasSiblingIdChange(): bool
    {
        return $this->courtOrder->getSibling()?->getId() !== $this->oldSiblingId;
    }

    public function hasKindChange(): bool
    {
        return $this->courtOrder->getOrderKind() !== $this->oldKind;
    }
}
