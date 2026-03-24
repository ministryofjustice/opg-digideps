<?php

declare(strict_types=1);

namespace App\Domain\CourtOrder;

enum CourtOrderKind: string
{
    /**
     * Only PFA or HW
     */
    case Single = 'single';
    /**
     * Both PFA and HW with identical sets of deputies
     */
    case Hybrid = 'hybrid';
    /**
     * Both PFA and HW with differing sets of deputies
     */
    case Dual = 'dual';
}
