<?php

declare(strict_types=1);

namespace App\v2\DTO;

use App\Entity\User;

/**
 * Encapsulate data required to invite someone to something in digideps.
 * Generally this will be a deputy being invited to a court order.
 */
class InviteeDTO
{
    public function __construct(
        public string $email,
        public string $firstname,
        public string $lastname,

        // one of the User::ROLE_* constants
        public string $roleName = User::ROLE_LAY_DEPUTY,
    ) {
    }

    public function isValid(): bool
    {
        return '' !== $this->email && '' !== $this->firstname && '' !== $this->lastname && '' !== $this->roleName;
    }
}
