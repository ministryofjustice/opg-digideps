<?php

declare(strict_types=1);

namespace App\Service\File;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImageConvertorTest extends KernelTestCase
{
    /** @test */
    public function convert()
    {
        $projectDir = self::bootKernel()->getProjectDir();
        $sut = new ImageConvertor();

        $filePath = sprintf('%s/tests/phpunit/TestData/good-heic.heic', $projectDir);
        [$body, $filename] = $sut->convert($filePath);

        self::assertEquals('good-heic.jpeg', $filename);
        $test = 'blah';
    }
}
