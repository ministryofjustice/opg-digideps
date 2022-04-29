<?php

declare(strict_types=1);

namespace App\Service\File;

enum SupportedOriginalFileType
{
    case JFIF;
    case HEIC;
    public function convertsTo(): string
    {
        return match ($this) {
            self::JFIF, self::HEIC => 'jpg',
        };
    }
}

class ImageConvertor
{
}
