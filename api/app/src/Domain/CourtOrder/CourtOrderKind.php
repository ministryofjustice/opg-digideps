<?php

declare(strict_types=1);

namespace App\Domain\CourtOrder;

enum CourtOrderKind: string
{
    case Single = 'single';
    case Hybrid = 'hybrid';
    case Dual = 'dual';
}
