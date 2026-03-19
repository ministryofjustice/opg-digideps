<?php

declare(strict_types=1);

namespace App\Domain\CourtOrder;

enum CourtOrderType: string
{
    case PFA = 'pfa';
    case HW = 'hw';
}
