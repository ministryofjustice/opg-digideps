<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\File\Scanner;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OPG\Digideps\Frontend\Service\File\Scanner\ClamFileScanner;
use OPG\Digideps\Frontend\Service\File\Scanner\Exception\VirusFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamFileScannerTest extends TestCase
{
    private LoggerInterface $logger;
    private array $badPdfKeywords = ['AcroForm', 'JavaScript'];
    private ?Client $client = null;

    protected function setUp(): void
    {
        $this->logger = self::createMock(LoggerInterface::class);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testScanFileReturnsGracefullyOnCleanFile(): void
    {
        $this->ensureFileWillBeClean()
            ->invokeTest('file.pdf');
    }

    public function testScanFileThrowsVirusFoundExceptionOnBadKeywordsFoundInPdf(): void
    {
        self::expectException(VirusFoundException::class);

        $this->invokeTest('contains-form.pdf');
    }

    public function testScanFileThrowsVirusFoundExceptionOnVirusFound(): void
    {
        self::expectException(VirusFoundException::class);

        $this->ensureVirusWillBeFound()
            ->ensureVirusWillBeLogged()
            ->invokeTest('file.pdf');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function tesScanFileMakesMultipleReattemptsIfScanServiceIsUnavailable(): void
    {
        $this->ensureServiceIsTemporarilyUnavailable()
            ->invokeTest('file.pdf');
    }

    public function testScanFileThrowsRuntimeExceptionIfServiceIsForeverUnavailable(): void
    {
        self::expectException(\RuntimeException::class);

        $this->ensureServiceIsForeverUnavailable()
            ->ensureErrorWillBeLogged()
            ->invokeTest('file.pdf');
    }

    private function ensureFileWillBeClean(): static
    {
        $response = new Response(200, [], 'Everything ok : true');
        $this->presetClientResponses([$response]);

        return $this;
    }

    private function ensureVirusWillBeFound(): static
    {
        $response = new Response(200, [], 'Everything ok : false');
        $this->presetClientResponses([$response]);

        return $this;
    }

    private function ensureServiceIsTemporarilyUnavailable(): static
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

    private function ensureServiceIsForeverUnavailable(): static
    {
        $mockResponses = [];

        // Mix of both types of response exceptions
        for ($i = 0; $i < ClamFileScanner::MAX_SCAN_ATTEMPTS / 2; ++$i) {
            $mockResponses[] = new ServerException('unavailable', new Request('get', 'test'), new Response(500));
            $mockResponses[] = new ConnectException('unavailable', new Request('get', 'test'));
        }

        // Ensure the MAX_SCAN_ATTEMPTS + 1 attempt would be good, to prove that
        // we quit trying before this request is made.
        $mockResponses[] = new Response(200, [], 'Everything ok : true');

        $this->presetClientResponses($mockResponses);

        return $this;
    }

    private function presetClientResponses(array $mockResponses): void
    {
        $handler = HandlerStack::create(new MockHandler($mockResponses));
        $this->client = new Client(['handler' => $handler]);
    }

    private function ensureVirusWillBeLogged(): static
    {
        $this->logger->expects(self::once())
            ->method('info')
            ->with('Scan result: virus found in file: file.pdf');

        return $this;
    }

    private function ensureErrorWillBeLogged(): static
    {
        $this->logger->expects(self::once())
            ->method('error')
            ->with('Scanner service down: unavailable');

        return $this;
    }

    /**
     * @throws GuzzleException
     */
    private function invokeTest($filename): void
    {
        if ($this->client === null) {
            $this->client = new Client();
        }
        $scanner = new ClamFileScanner($this->client, $this->logger, $this->badPdfKeywords);
        $scanner->scanFile(new UploadedFile(__DIR__ . "/$filename", $filename));
    }
}
