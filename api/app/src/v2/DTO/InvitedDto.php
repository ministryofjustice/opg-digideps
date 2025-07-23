<?php

declare(strict_types=1);

namespace App\v2\DTO;

/**
 * Holds data about the outcome of an attempt to create an invite.
 */
class InvitedDto
{
    public function __construct(
        public readonly string $courtOrderUid,
        public readonly int $invitingUserId,
        public bool $success = false,
        public ?string $outcome = null,
        public ?string $invitedDeputyUid = null,
        public ?int $invitedUserId = null,
    ) {
    }

    public function setOutcome(string $outcome): static
    {
        $this->outcome = $outcome;

        return $this;
    }
}
