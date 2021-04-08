<?php declare(strict_types=1);


namespace App\Service\File;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileNameFixerTest extends KernelTestCase
{
    private string $projectDir;
    private FileNameFixer $sut;

    public function setUp(): void
    {
        $this->projectDir = self::bootKernel()->getProjectDir();
        $this->sut = new FileNameFixer(new FinfoMimeTypeDetector());
    }

    /**
     * @test
     * @dataProvider fileNameProvider
     */
    public function removeWhiteSpaceBeforeFileExtension(string $originalFileName, string $expectedFileName)
    {
        $fixedFilename = $this->sut->removeWhiteSpaceBeforeFileExtension($originalFileName);
        self::assertEquals($expectedFileName, $fixedFilename);
    }

    public function fileNameProvider()
    {
        return [
            'valid filename' => ['dd_fileuploadertest.pdf', 'dd_fileuploadertest.pdf'],
            'single whitespace character is removed' => ['dd_fileuploadertest .pdf', 'dd_fileuploadertest.pdf'],
            'multiple whitespace characters are removed' => ['dd_fileuploadertest     .pdf', 'dd_fileuploadertest.pdf'],
            'file with full stops' => ['dd_fileuploadertest 1.2.3     .pdf', 'dd_fileuploadertest 1.2.3.pdf'],
        ];
    }

    /**
     * @dataProvider missingExtensionFilesProvider
     * @test
     */
    public function addMissingFileExtension(string $relativeFilePath, string $fileName, string $expectedFilename)
    {
        $filePath = sprintf('%s/%s', $this->projectDir, $relativeFilePath);
        $uploadedFile = new UploadedFile($filePath, $fileName);
        $fileBody = file_get_contents($filePath);
        $alteredFileName = $this->sut->addMissingFileExtension($uploadedFile, $fileBody);

        self::assertEquals($expectedFilename, $alteredFileName);
    }

    public function missingExtensionFilesProvider()
    {
        return [
            'jpeg' => ['tests/phpunit/TestData/good-jpeg', 'good-jpeg', 'good-jpeg.jpeg'],
            'pdf' => ['tests/phpunit/TestData/good-pdf', 'good-pdf', 'good-pdf.pdf'],
            'png' => ['tests/phpunit/TestData/good-png', 'good-png', 'good-png.png'],
            'Already has an extension' => ['tests/phpunit/TestData/good-jpeg.jpeg', 'good-jpeg.jpeg', 'good-jpeg.jpeg'],
        ];
    }
}
