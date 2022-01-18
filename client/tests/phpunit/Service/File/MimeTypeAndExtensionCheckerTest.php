<?php

namespace App\Service\File;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MimeTypeAndExtensionCheckerTest extends KernelTestCase
{
    private string $projectDir;
    private MimeTypeAndExtensionChecker $sut;

    public function setUp(): void
    {
        $this->projectDir = self::bootKernel()->getProjectDir();
        $this->sut = new MimeTypeAndExtensionChecker(new FinfoMimeTypeDetector());
    }

    /**
     * @test
     * @dataProvider  fileProvider
     */
    public function check(string $relativePath, string $fileName, bool $extensionMatchesMimetype)
    {
        $filePath = sprintf('%s/%s', $this->projectDir, $relativePath);
        $uploadedFile = new UploadedFile($filePath, $fileName);
        $fileBody = file_get_contents($filePath);

        $extensionAndMimeTypeMatch = $this->sut->check($uploadedFile, $fileBody);
        self::assertEquals($extensionMatchesMimetype, $extensionAndMimeTypeMatch);
    }

    public function fileProvider()
    {
        return [
            'matching JPEG' => ['tests/phpunit/TestData/jpeg-file.jpeg', 'jpeg-file.jpeg', true],
            'matching PNG' => ['tests/phpunit/TestData/png-file.png', 'png-file.png', true],
            'matching PDF' => ['tests/phpunit/TestData/pdf-file.pdf', 'pdf-file.pdf', true],
            'not matching JPEG' => ['tests/phpunit/TestData/png-file.jpeg', 'png-file.jpeg', false],
            'not matching PNG' => ['tests/phpunit/TestData/pdf-file.png', 'pdf-file.png', false],
            'not matching PDF' => ['tests/phpunit/TestData/jpeg-file.pdf', 'jpeg-file.pdf', false],
        ];
    }
}
