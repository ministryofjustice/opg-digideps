<?php

declare(strict_types=1);

namespace App\Service\File;

use finfo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImageConvertorTest extends KernelTestCase
{
    /** @test */
    public function convert()
    {
        $projectDir = self::bootKernel()->getProjectDir();
        $sut = new ImageConvertor();
        $fileInfo = new finfo(FILEINFO_MIME);

        $filePath = sprintf('%s/tests/phpunit/TestData/good-heic.heic', $projectDir);
        self::assertStringContainsString('image/heif', $fileInfo->buffer(file_get_contents($filePath)));

        [$body, $filename] = $sut->convert($filePath, $filePath);

        self::assertEquals('good-heic.jpeg', $filename);
        self::assertStringContainsString('image/jpeg', $fileInfo->buffer($body));
    }
}
