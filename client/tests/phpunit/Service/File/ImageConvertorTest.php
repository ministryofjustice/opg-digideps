<?php

declare(strict_types=1);

namespace App\Service\File;

use PHPUnit\Framework\TestCase;

class ImageConvertorTest extends TestCase
{
    public function testConvertImageTo()
    {
        $sut = new ImageConvertor();
        $sut->convertImageTo($image, SupportedTargetFileType::JFIF)

    }
}
