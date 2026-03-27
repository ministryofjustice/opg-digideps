<?php

declare(strict_types=1);

namespace App\Domain\CourtOrder;

enum CourtOrderType: string
{
    /**
     * Property and affairs
     */
    case PFA = 'pfa';
    /**
     * Health and welfare
     */
    case HW = 'hw';
}
