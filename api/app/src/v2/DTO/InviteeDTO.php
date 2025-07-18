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
        public ?string $email = null,
        public ?string $firstname = null,
        public ?string $lastname = null,

        // one of the User::ROLE_* constants
        public string $role_name = User::ROLE_LAY_DEPUTY,
    ) {
    }
}
