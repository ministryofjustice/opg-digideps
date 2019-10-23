<?php

namespace AppBundle\Service\File\Scanner;

use AppBundle\Service\File\Scanner\Exception\VirusFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamFileScannerTest extends TestCase
{
    /** @var ClamFileScanner */
    private $scanner;

    /** @var LoggerInterface | MockObject */
    private $logger;

    /** @var array */
    private $badPdfKeywords;

    /** @var Client */
    private $client;

    /** mixed */
    private $result;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->badPdfKeywords = ['AcroForm', 'JavaScript'];
    }

    /**
     * @test
     */
    public function scanFile_returnsGracefullyOnCleanFile()
    {
        $this
            ->ensureFileWillBeClean()
            ->invokeTest('file.pdf')
            ->assertGracefulReturn();
    }

    /**
     * @test
     */
    public function scanFile_throws_VirusFoundException_onBadKeywordsFoundInPdf()
    {
        $this->expectException(VirusFoundException::class);

        $this->client = new Client();
        $this->invokeTest('contains-form.pdf');
    }

    /**
     * @test
     */
    public function scanFile_throws_VirusFoundException_onVirusFound()
    {
        $this->expectException(VirusFoundException::class);

        $this
            ->ensureVirusWillBeFound()
            ->ensureVirusWillBeLogged()
            ->invokeTest('file.pdf');
    }

    /**
     * @test
     */
    public function scanFile_makesMultipleReattemptsIfScanServiceIsUnavailable()
    {
        $this
            ->ensureServiceIsTemporarilyUnavailable()
            ->invokeTest('file.pdf')
            ->assertGracefulReturn();
    }

    /**
     * @test
     */
    public function scanFile_throws_RuntimeException_ifServiceIsForeverUnavailable()
    {
        $this->expectException(\RuntimeException::class);

        $this
            ->ensureServiceIsForeverUnavailable()
            ->ensureCriticalWillBeLogged()
            ->invokeTest('file.pdf');
    }

    /**
     * @return ClamFileScannerTest
     */
    private function ensureFileWillBeClean(): ClamFileScannerTest
    {
        $response = new Response(200, [], 'Everything ok : true');
        $this->presetClientResponses([$response]);

        return $this;
    }

    /**
     * @return ClamFileScannerTest
     */
    private function ensureVirusWillBeFound(): ClamFileScannerTest
    {
        $response = new Response(200, [], 'Everything ok : false');
        $this->presetClientResponses([$response]);

        return $this;
    }

    /**
     * @return ClamFileScannerTest
     */
    private function ensureServiceIsTemporarilyUnavailable(): ClamFileScannerTest
    {
        $mockResponses = [];
        // Ensures all but the last attempt is unsuccessful
        for ($i = 0; $i < ClamFileScanner::MAX_SCAN_ATTEMPTS - 1; $i++) {
            $mockResponses[] = new ServerException('unavailable', new Request('get', 'test'));
        }

        // Final attempt is good.
        $mockResponses[] = new Response(200, [], 'Everything ok : true');

        $this->presetClientResponses($mockResponses);

        return $this;
    }

    /**
     * @return ClamFileScannerTest
     */
    private function ensureServiceIsForeverUnavailable(): ClamFileScannerTest
    {
        $mockResponses = [];
        // Mix of both types of response exceptions
        for ($i = 0; $i < ClamFileScanner::MAX_SCAN_ATTEMPTS / 2; $i++) {
            $mockResponses[] = new ServerException('unavailable', new Request('get', 'test'));
            $mockResponses[] = new ConnectException('unavailable', new Request('get', 'test'));
        }


        // Ensure the MAX_SCAN_ATTEMPTS + 1 attempt would be good, to prove that we quit trying before this request is made.
        $mockResponses[] = new Response(200, [], 'Everything ok : true');

        $this->presetClientResponses($mockResponses);

        return $this;
    }

    /**
     * @param array $mockResponses
     */
    private function presetClientResponses(array $mockResponses): void
    {
        $handler = HandlerStack::create(new MockHandler($mockResponses));
        $this->client = new Client(['handler' => $handler]);

    }

    /**
     * @return ClamFileScannerTest
     */
    private function ensureVirusWillBeLogged(): ClamFileScannerTest
    {
        $this
            ->logger
            ->expects($this->once())
            ->method('info')
            ->with('Scan result: virus found in file: file.pdf');

        return $this;
    }

    /**
     * @return ClamFileScannerTest
     */
    private function ensureCriticalWillBeLogged(): ClamFileScannerTest
    {
        $this
            ->logger
            ->expects($this->once())
            ->method('critical')
            ->with('Scanner service down: unavailable');

        return $this;
    }

    /**
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function invokeTest($filename): ClamFileScannerTest
    {
        $this->scanner = new ClamFileScanner($this->client, $this->logger, $this->badPdfKeywords);
        $this->result = $this->scanner->scanFile(new UploadedFile(__DIR__."/$filename", $filename));

        return $this;
    }

    private function assertGracefulReturn()
    {
        $this->assertTrue($this->result);
    }
}
