<?php

declare(strict_types=1);

namespace App\Domain\CourtOrder;

enum CourtOrderReportType: string
{
    /**
     * Property and affairs - High assets
     */
    case OPG102 = 'OPG102';
    /**
     * Property and affairs - Low assets
     */
    case OPG103 = 'OPG103';
    /**
     * Health and welfare - Not hybrid
     */
    case OPG104 = 'OPG104';

    public function getSuffix(): string
    {
        return substr($this->value, -1);
    }
}
