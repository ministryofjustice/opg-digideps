<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

enum ClientStatus: string
{
    case DEATH_CONFIRMED = 'DEATH_CONFIRMED';
    case ACTIVE = 'ACTIVE';
}
