<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Entity\CourtOrder;

final class CourtOrderRelationshipChange
{
    public ?CourtOrderKind $newKind = null;
    public ?int $newSiblingId = null;

    public function __construct(
        public readonly CourtOrder $courtOrder,
        public readonly ?CourtOrderKind $oldKind = null,
        public readonly ?int $oldSiblingId = null
    ) {
        $this->newKind = $this->courtOrder->getOrderKind();
        $this->newSiblingId = $this->courtOrder->getSibling()?->getId();
    }

    public function hasSiblingIdChange(): bool
    {
        return $this->newSiblingId !== $this->oldSiblingId;
    }

    public function hasKindChange(): bool
    {
        return $this->newKind !== $this->oldKind;
    }
}
