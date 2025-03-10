<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

enum DeputyStatusOnOrder: string
{
    case DISCHARGED = 'DISCHARGED';
    case ACTIVE = 'ACTIVE';
    case OPEN = 'OPEN';
}
