<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Entity\CourtOrder;

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
