<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

enum OrderType: string
{
    case PFA = 'pfa';
    case HW = 'hw';
}
