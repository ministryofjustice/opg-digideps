<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\File;

use Orbitale\Component\ImageMagick\Command;

class ImageMagickFactory
{
    public function create(): Command
    {
        return new Command();
    }
}
