<?php

declare(strict_types=1);

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
     *
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
     *
     * @test
     */
    public function addMissingFileExtension(string $relativeFilePath, string $fileName, string $expectedFilename)
    {
        $filePath = sprintf('%s/%s', $this->projectDir, $relativeFilePath);
        $uploadedFile = new UploadedFile($filePath, $fileName);
        $alteredFileName = $this->sut->addMissingFileExtension($uploadedFile);

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

    /**
     * @test
     *
     * @dataProvider unusualCharactersProvider
     */
    public function removeUnusualCharacters($fileName, $expectedFileName)
    {
        $actualFileName = $this->sut->removeUnusualCharacters($fileName);

        self::assertEquals($expectedFileName, $actualFileName);
    }

    public function unusualCharactersProvider()
    {
        return [
            'white space is transformed to underscores' => ['My File 1 2nd revision.pdf', 'My_File_1_2nd_revision.pdf'],
            'all special characters are removed' => ['$%/[]My {}{}|{}File_+()=<>1.pdf', 'My_File_1.pdf'],
            'file extension dot remains, any others transformed to underscores' => ['My_File.png.pdf', 'My_File_png.pdf'],
            'HTML constructs are not allowed' => ['<a href="myhostilefile.exe">Report</a>.pdf', 'a_hrefmyhostilefile_exeReporta.pdf'],
            'Directory constructs are not allowed' => ['../../', '___.'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider uploadedFileDataProvider
     */
    public function lowerCaseFileExtension($filePath, $uploadedFileNameAndExtension, $expectedFileNameAndExtension)
    {
        $uploadedFile = new UploadedFile(sprintf('%s'.$filePath, $this->projectDir), $uploadedFileNameAndExtension);
        $uploadedFileExtensionCheck = $this->sut->lowerCaseFileExtension($uploadedFile);

        self::assertEquals($expectedFileNameAndExtension, $uploadedFileExtensionCheck->getClientOriginalName());
    }

    public function uploadedFileDataProvider()
    {
        return [
            'file extension is lowercased' => ['/tests/phpunit/TestData/upperCaseFileExt.PNG', 'upperCaseFileExt.PNG', 'upperCaseFileExt.png'],
            'file extension remains the same' => ['/tests/phpunit/TestData/good-jpeg.jpeg', 'good-jpeg.jpeg', 'good-jpeg.jpeg'],
        ];
    }
}
