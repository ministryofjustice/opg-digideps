<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Model;

/**
 * Holds responses to requests to invite a deputy.
 * Maps onto InvitedDto.
 */
class InviteResult
{
    public function __construct(
        public bool $success,
        public ?string $message = null,
    ) {
    }
}
