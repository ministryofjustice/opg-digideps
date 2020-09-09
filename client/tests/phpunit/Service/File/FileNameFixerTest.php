<?php declare(strict_types=1);

namespace AppBundle\Service\File;

use PHPUnit\Framework\TestCase;

class FileNameFixerTest extends TestCase
{
    /**
     * @test
     * @dataProvider fileNameProvider
     */
    public function removeWhiteSpaceBeforeFileExtension(string $originalFileName, string $expectedFileName)
    {
        $fixedFilename = FileNameFixer::removeWhiteSpaceBeforeFileExtension($originalFileName);
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
}
