<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\CourtOrder;

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

    /**
     * Allows derivation from raw report types like 102 or '104'
     */
    public static function permissiveTryFrom(int|string $value): ?CourtOrderReportType
    {
        if (is_int($value) || !str_starts_with($value, 'OPG')) {
            $value = "OPG$value";
        }

        return self::tryFrom($value);
    }
}
