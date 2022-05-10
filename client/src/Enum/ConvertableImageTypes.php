<?php

declare(strict_types=1);

namespace App\Enum;

enum ConvertableImageTypes: string
{
    case JFIF = 'jfif';
    case HEIC = 'heic';
    public function convertsTo(): string
    {
        return match ($this) {
            self::JFIF, self::HEIC => 'jpeg',
        };
    }
}
