<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Types\UploadableFileInterface;
use AppBundle\Service\File\Types\Pdf;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Post\PostFile;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use \Mockery as m;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ClamAVCheckerTest
 *
 * @package AppBundle\Service\File\Checker
 */
class ClamAVCheckerTest extends MockeryTestCase
{
    /**
     * @var ClamAVChecker
     */
    private $sut;

    public function testCheckFileForGoodPdf()
    {
        $this->markTestSkipped();
        $location = '1234';
        $mockRequest = m::mock(RequestInterface::class);

        $mockHttpClient = m::mock(ClientInterface::class);
        $mockHttpClient->shouldReceive('send')
            ->with($mockRequest)
            ->andReturn(
                $this->generateResponse(['location' => $location])
            );

        $mockBody = m::mock();
        $mockBody->shouldReceive('addFile')->with(m::type(PostFile::class));

        $mockHttpClient->shouldReceive('createRequest')->with('POST', 'upload')->andReturn($mockRequest);
        $mockHttpClient->shouldReceive('get')->with($location)->andReturn(
            $this->generateResponse(['file_scanner_result' => 'PASS'])
        );

        $mockRequest->shouldReceive('getBody')->andReturn($mockBody);

        $mockLogger = $this->getMockLogger();

        $this->sut = new ClamAVChecker($mockHttpClient, $mockLogger);

        $mockPostFile = m::mock(UploadableFileInterface::class);

        $file = $this->generateFileByType(Pdf::class);

        $uploadedFile = new UploadedFile('../fixtures/good.pdf', 'good.pdf');

        $file->setUploadedFile($uploadedFile);

        $result = $this->sut->checkFile($file);

        $this->assertSame($file, $result);
        $this->assertEquals($file->getScanResult(), ['file_scanner_result' => 'PASS']);

    }

    public function testCheckFileScannerNotAvailable()
    {
        $this->markTestSkipped();
        $location = '1234';
        $mockRequest = m::mock(RequestInterface::class);

        $mockHttpClient = m::mock(ClientInterface::class);
        $mockHttpClient->shouldReceive('send')
            ->with($mockRequest)
            ->andReturnNull();

        $mockBody = m::mock();
        $mockBody->shouldReceive('addFile')->with(m::type(PostFile::class));

        $mockLogger = $this->getMockLogger();

        $this->sut = new ClamAVChecker($mockHttpClient, $mockLogger);

        $file = $this->generateFileByType(Pdf::class);

        $uploadedFile = new UploadedFile('../fixtures/good.pdf', 'good.pdf');

        $file->setUploadedFile($uploadedFile);

        $this->setExpectedException(\RuntimeException::class);

        $result = $this->sut->checkFile($file);

    }

    public function testCheckFileStatusNotAvailable()
    {
        $this->markTestSkipped();
        $location = '1234';
        $mockRequest = m::mock(RequestInterface::class);

        $mockHttpClient = m::mock(ClientInterface::class);
        $mockHttpClient->shouldReceive('send')
            ->with($mockRequest)
            ->andReturn(
                $this->generateResponse(['location' => $location])
            );

        $mockBody = m::mock();
        $mockBody->shouldReceive('addFile')->with(m::type(PostFile::class));

        $mockHttpClient->shouldReceive('createRequest')->with('POST', 'upload')->andReturn($mockRequest);
        $mockHttpClient->shouldReceive('get')->with($location)->andReturnNull();

        $mockLogger = $this->getMockLogger();

        $this->sut = new ClamAVChecker($mockHttpClient, $mockLogger);

        $mockPostFile = m::mock(UploadableFileInterface::class);

        $file = $this->generateFileByType(Pdf::class);

        $uploadedFile = new UploadedFile('../fixtures/good.pdf', 'good.pdf');

        $file->setUploadedFile($uploadedFile);

        $this->setExpectedException(\RuntimeException::class);

        $result = $this->sut->checkFile($file);
    }

    public function testCheckFileForVirusPdf()
    {
        $this->markTestSkipped();
        $location = '1234';
        $mockRequest = m::mock(RequestInterface::class);

        $mockHttpClient = m::mock(ClientInterface::class);
        $mockHttpClient->shouldReceive('send')
            ->with($mockRequest)
            ->andReturn(
                $this->generateResponse(['location' => $location])
            );

        $mockBody = m::mock();
        $mockBody->shouldReceive('addFile')->with(m::type(PostFile::class));

        $mockHttpClient->shouldReceive('createRequest')->with('POST', 'upload')->andReturn($mockRequest);
        $mockHttpClient->shouldReceive('get')->with($location)->andReturn(
            $this->generateResponse(['file_scanner_result' => 'FAIL', 'av_scan_result' => 'FAIL'])
        );

        $mockRequest->shouldReceive('getBody')->andReturn($mockBody);

        $mockLogger = $this->getMockLogger();

        $this->sut = new ClamAVChecker($mockHttpClient, $mockLogger);

        $mockPostFile = m::mock(UploadableFileInterface::class);

        $file = $this->generateFileByType(Pdf::class);

        $uploadedFile = new UploadedFile('../fixtures/good.pdf', 'good.pdf');

        $file->setUploadedFile($uploadedFile);

        $this->setExpectedException(VirusFoundException::class);

        $result = $this->sut->checkFile($file);
    }

    public function testCheckFileForRiskyPdf()
    {
        $this->markTestSkipped();
        $location = '1234';
        $mockRequest = m::mock(RequestInterface::class);

        $mockHttpClient = m::mock(ClientInterface::class);
        $mockHttpClient->shouldReceive('send')
            ->with($mockRequest)
            ->andReturn(
                $this->generateResponse(['location' => $location])
            );

        $mockBody = m::mock();
        $mockBody->shouldReceive('addFile')->with(m::type(PostFile::class));

        $mockHttpClient->shouldReceive('createRequest')->with('POST', 'upload')->andReturn($mockRequest);
        $mockHttpClient->shouldReceive('get')->with($location)->andReturn(
            $this->generateResponse(['file_scanner_result' => 'FAIL', 'pdf_scan_result' => 'FAIL', 'av_scan_result' => 'PASS'])
        );

        $mockRequest->shouldReceive('getBody')->andReturn($mockBody);

        $mockLogger = $this->getMockLogger();

        $this->sut = new ClamAVChecker($mockHttpClient, $mockLogger);

        $mockPostFile = m::mock(UploadableFileInterface::class);

        $file = $this->generateFileByType(Pdf::class);

        $uploadedFile = new UploadedFile('../fixtures/good.pdf', 'good.pdf');

        $file->setUploadedFile($uploadedFile);

        $this->setExpectedException(RiskyFileException::class);

        $result = $this->sut->checkFile($file);
    }

    /**
     * @param $scanResult
     * @return m\MockInterface
     */
    private function generateResponse($scanResult)
    {
        $mockResponse = m::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody')
            ->andReturnSelf()
            ->shouldReceive('getContents')
            ->andReturn(json_encode($scanResult));

        return $mockResponse;
    }

    /**
     * Generates FileType object
     *
     * @param $type
     * @return mixed
     */
    private function generateFileByType($type)
    {
        $mockVirusChecker = m::mock(FileCheckerInterface::class);
        $mockFileTypeChecker = m::mock(FileCheckerInterface::class);
        $mockLogger = $this->getMockLogger();

        $file = new $type($mockVirusChecker, $mockFileTypeChecker, $mockLogger);

        return $file;
    }

    /**
     * @return m\MockInterface
     */
    private function getMockLogger()
    {
        $mockLogger = m::mock(LoggerInterface::class);
        $mockLogger->shouldReceive('debug')->with(m::type('string'));
        $mockLogger->shouldReceive('info')->with(m::type('string'));
        $mockLogger->shouldReceive('warning')->with(m::type('string'));
        $mockLogger->shouldReceive('critical')->with(m::type('string'));

        return $mockLogger;
    }

}
