<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;

final readonly class CourtOrderRelationshipChange
{
    public function __construct(
        public int $courtOrderId,
        public CourtOrderKind $currentKind,
        public ?int $currentSiblingId = null,
        public ?CourtOrderKind $oldKind = null,
        public ?int $oldSiblingId = null
    ) {
    }

    public function hasSiblingIdChange(): bool
    {
        return $this->currentSiblingId !== $this->oldSiblingId;
    }

    public function hasKindChange(): bool
    {
        return $this->currentKind !== $this->oldKind;
    }
}
