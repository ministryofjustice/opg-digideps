<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Deputy;

enum DeputyType: string
{
    /**
     * Lay Deputy
     */
    case LAY = 'LAY';
    /**
     * Professional Deputy
     */
    case PRO = 'PRO';
    /**
     * Public Authority
     */
    case PA = 'PA';

    public function getSuffix(): string
    {
        return match ($this) {
            self::LAY => '',
            self::PRO => '5',
            self::PA => '6',
        };
    }
}
