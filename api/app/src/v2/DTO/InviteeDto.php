<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\DTO;

use OPG\Digideps\Backend\Entity\User;

/**
 * Encapsulate data required to invite someone to something in digideps.
 * Generally this will be a deputy being invited to a court order.
 */
class InviteeDto
{
    public function __construct(
        public string $email,
        public string $firstname,
        public string $lastname,
        // one of the User::ROLE_* constants
        public string $roleName = User::ROLE_LAY_DEPUTY,
    ) {
    }
}
