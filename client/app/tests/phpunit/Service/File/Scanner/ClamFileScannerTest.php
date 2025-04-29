<?php

namespace App\Service\File\Scanner;

use App\Service\File\Scanner\Exception\VirusFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamFileScannerTest extends TestCase
{
    private LoggerInterface $logger;
    private array $badPdfKeywords;
    private Client $client;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->badPdfKeywords = ['AcroForm', 'JavaScript'];
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function scanFileReturnsGracefullyOnCleanFile(): void
    {
        $this
            ->ensureFileWillBeClean()
            ->invokeTest('file.pdf');
    }

    /**
     * @test
     */
    public function scanFileThrowsVirusFoundExceptionOnBadKeywordsFoundInPdf(): void
    {
        $this->expectException(VirusFoundException::class);

        $this->client = new Client();
        $this->invokeTest('contains-form.pdf');
    }

    /**
     * @test
     */
    public function scanFileThrowsVirusFoundExceptionOnVirusFound(): void
    {
        $this->expectException(VirusFoundException::class);

        $this
            ->ensureVirusWillBeFound()
            ->ensureVirusWillBeLogged()
            ->invokeTest('file.pdf');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function scanFileMakesMultipleReattemptsIfScanServiceIsUnavailable(): void
    {
        $this
            ->ensureServiceIsTemporarilyUnavailable()
            ->invokeTest('file.pdf');
    }

    /**
     * @test
     */
    public function scanFileThrowsRuntimeExceptionIfServiceIsForeverUnavailable(): void
    {
        $this->expectException(RuntimeException::class);

        $this
            ->ensureServiceIsForeverUnavailable()
            ->ensureErrorWillBeLogged()
            ->invokeTest('file.pdf');
    }

    private function ensureFileWillBeClean(): ClamFileScannerTest
    {
        $response = new Response(200, [], 'Everything ok : true');
        $this->presetClientResponses([$response]);

        return $this;
    }

    private function ensureVirusWillBeFound(): ClamFileScannerTest
    {
        $response = new Response(200, [], 'Everything ok : false');
        $this->presetClientResponses([$response]);

        return $this;
    }

    private function ensureServiceIsTemporarilyUnavailable(): ClamFileScannerTest
    {
        $mockResponses = [];
        // Ensures all but the last attempt is unsuccessful
        for ($i = 0; $i < ClamFileScanner::MAX_SCAN_ATTEMPTS - 1; ++$i) {
            $mockResponses[] = new ServerException('unavailable', new Request('get', 'test'), new Response(400));
        }

        // Final attempt is good.
        $mockResponses[] = new Response(200, [], 'Everything ok : true');

        $this->presetClientResponses($mockResponses);

        return $this;
    }

    private function ensureServiceIsForeverUnavailable(): ClamFileScannerTest
    {
        $mockResponses = [];
        // Mix of both types of response exceptions
        for ($i = 0; $i < ClamFileScanner::MAX_SCAN_ATTEMPTS / 2; ++$i) {
            $mockResponses[] = new ServerException('unavailable', new Request('get', 'test'), new Response(500));
            $mockResponses[] = new ConnectException('unavailable', new Request('get', 'test'));
        }

        // Ensure the MAX_SCAN_ATTEMPTS + 1 attempt would be good, to prove that we quit trying before this request is made.
        $mockResponses[] = new Response(200, [], 'Everything ok : true');

        $this->presetClientResponses($mockResponses);

        return $this;
    }

    private function presetClientResponses(array $mockResponses): void
    {
        $handler = HandlerStack::create(new MockHandler($mockResponses));
        $this->client = new Client(['handler' => $handler]);
    }

    private function ensureVirusWillBeLogged(): ClamFileScannerTest
    {
        $this
            ->logger
            ->expects($this->once())
            ->method('info')
            ->with('Scan result: virus found in file: file.pdf');

        return $this;
    }

    private function ensureErrorWillBeLogged(): ClamFileScannerTest
    {
        $this
            ->logger
            ->expects($this->once())
            ->method('error')
            ->with('Scanner service down: unavailable');

        return $this;
    }

    /**
     * @throws GuzzleException
     */
    private function invokeTest($filename): void
    {
        $scanner = new ClamFileScanner($this->client, $this->logger, $this->badPdfKeywords);
        $scanner->scanFile(new UploadedFile(__DIR__."/$filename", $filename));
    }
}
