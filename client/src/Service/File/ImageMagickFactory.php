<?php

declare(strict_types=1);

namespace App\Service\File;

use Orbitale\Component\ImageMagick\Command;

class ImageMagickFactory
{
    public function create(): Command
    {
        return new Command();
    }
}
