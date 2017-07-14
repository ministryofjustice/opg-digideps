<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Types\UploadableFileInterface;
use AppBundle\Service\File\Types\Pdf;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use \Mockery as m;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class PdfCheckerTest
 * Initially this only checks the file extension is valid for the file type.
 *
 * @package AppBundle\Service\File\Checker
 */
class PdfCheckerTest extends MockeryTestCase
{
    /**
     * @var PdfChecker
     */
    private $sut;

    public function setUp()
    {
        $this->sut = new PdfChecker();
    }

    public function testCheckFileForGoodPdf()
    {
        $mockVirusChecker = m::mock(FileCheckerInterface::class);
        $mockFileTypeChecker = m::mock(FileCheckerInterface::class);
        $mockLogger = m::mock(LoggerInterface::class);

        $file = new Pdf($mockVirusChecker, $mockFileTypeChecker, $mockLogger);

        $uploadedFile = m::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('guessExtension')->andReturn('pdf');
        $uploadedFile->shouldReceive('getClientOriginalExtension')->andReturn('pdf');

        $file->setUploadedFile($uploadedFile);

        $result = $this->sut->checkFile($file);

    }

    public function testCheckFileForBadPdf()
    {
        $mockVirusChecker = m::mock(FileCheckerInterface::class);
        $mockFileTypeChecker = m::mock(FileCheckerInterface::class);
        $mockLogger = m::mock(LoggerInterface::class);

        $file = new Pdf($mockVirusChecker, $mockFileTypeChecker, $mockLogger);

        $uploadedFile = m::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('guessExtension')->andReturn('pdf');
        $uploadedFile->shouldReceive('getClientOriginalExtension')->andReturn('not-pdf');

        $file->setUploadedFile($uploadedFile);

        $this->setExpectedException(RiskyFileException::class);

        $result = $this->sut->checkFile($file);
    }
}
