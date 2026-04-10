<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\File;

use finfo;
use OPG\Digideps\Frontend\Service\File\ImageConvertor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImageConvertorTest extends KernelTestCase
{
    /** @test */
    public function convert()
    {
        $projectDir = self::bootKernel()->getProjectDir();
        $sut = new ImageConvertor();
        $fileInfo = new finfo(FILEINFO_MIME);

        $filePath = sprintf('%s/tests/Unit/TestData/good-heic.heic', $projectDir);
        self::assertStringContainsString('image/heif', $fileInfo->buffer(file_get_contents($filePath)));

        [$body, $filename] = $sut->convert($filePath, $filePath);

        self::assertEquals('good-heic.jpeg', $filename);
        self::assertStringContainsString('image/jpeg', $fileInfo->buffer($body));
    }
}
