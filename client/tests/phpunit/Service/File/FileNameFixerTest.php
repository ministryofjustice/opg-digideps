<?php declare(strict_types=1);

namespace AppBundle\Service\File;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FileNameFixerTest extends KernelTestCase
{
    private string $projectDir;

    public function setUp(): void
    {
        $this->projectDir = (self::bootKernel())->getProjectDir();
    }

    /**
     * @test
     * @dataProvider fileNameProvider
     */
    public function removeWhiteSpaceBeforeFileExtension(string $originalFileName, string $expectedFileName)
    {
        $sut = new FileNameFixer(new FinfoMimeTypeDetector());
        $fixedFilename = $sut->removeWhiteSpaceBeforeFileExtension($originalFileName);
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
    public function addMissingFileExtension(string $relativeFilePath, string $expectedFilename)
    {
        $sut = new FileNameFixer(new FinfoMimeTypeDetector(), $this->projectDir);
        $alteredFileName = $sut->addMissingFileExtension($relativeFilePath);

        self::assertEquals($expectedFilename, $alteredFileName);
    }

    public function missingExtensionFilesProvider()
    {
        return [
            'jpg' => ['/tests/TestData/good-jpg', 'good-jpg.jpg'],
            'pdf' => ['/tests/TestData/good-pdf', 'good-pdf.pdf'],
            'png' => ['/tests/TestData/good-png', 'good-png.png'],
        ];
    }
}
