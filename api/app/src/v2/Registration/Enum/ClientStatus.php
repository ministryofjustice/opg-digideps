<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

enum ClientStatus: string
{
    case Open = 'OPEN';
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';
    case Deceased = 'DECEASED';
    case Closed = 'CLOSED';
    case Duplicate = 'DUPLICATE';
    case RegainedCapacity = 'REGAINED_CAPACITY';
    case DeathNotified = 'DEATH_NOTIFIED';
    case DeathConfirmed = 'DEATH_CONFIRMED';
}
