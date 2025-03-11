<?php

namespace App\Service\File\Scanner;

use App\Service\File\Scanner\Exception\VirusFoundException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamFileScanner
{
    /** @var ClientInterface */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    /** @var array */
    private $badPdfKeywords;

    /** @var int */
    public const MAX_SCAN_ATTEMPTS = 90;

    public function __construct(ClientInterface $client, LoggerInterface $logger, array $badPdfKeywords)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->badPdfKeywords = $badPdfKeywords;
    }

    /**
     * @throws GuzzleException
     */
    public function scanFile(UploadedFile $file)
    {
        if ($this->fileIsPdf($file) && $this->pdfContainsBadKeywords($file)) {
            $this->logger->info(sprintf('Scan result: bad keyword found in file: %s', $file->getClientOriginalName()));
            throw new VirusFoundException();
        }

        $attempts = 0;
        while ($attempts < self::MAX_SCAN_ATTEMPTS) {
            try {
                ++$attempts;
                $response = $this->attemptScan($file);

                if (!$this->scanResultIsPass($response)) {
                    $this->logger->info(sprintf('Scan result: virus found in file: %s', $file->getClientOriginalName()));
                    throw new VirusFoundException();
                }
                break;
            } catch (GuzzleException $e) {
                if ($attempts >= self::MAX_SCAN_ATTEMPTS) {
                    $this->logger->error(sprintf('Scanner service down: %s', $e->getMessage()));
                    throw new \RuntimeException('Scanner service not available');
                }
            }
        }
    }

    private function fileIsPdf(UploadedFile $file): bool
    {
        $getClientOriginalExtensionLower = is_null($file->getClientOriginalExtension()) ? '' : strtolower($file->getClientOriginalExtension());

        return 'pdf' === $getClientOriginalExtensionLower;
    }

    private function pdfContainsBadKeywords(UploadedFile $file): bool
    {
        $regex = sprintf('/<<\s*\/(%s)/', implode('|', $this->badPdfKeywords));
        $contents = file_get_contents($file->getPathname());

        return (bool) preg_match($regex, $contents);
    }

    /**
     * @return mixed|ResponseInterface
     *
     * @throws GuzzleException
     */
    private function attemptScan(UploadedFile $file): ResponseInterface
    {
        return $this
            ->client
            ->request('POST', '/scan', [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($file->getPathName(), 'r'),
                    ],
                ],
            ]);
    }

    private function scanResultIsPass(Response $response): bool
    {
        $result = explode(':', trim($response->getBody()->getContents()));

        return 'true' === trim(end($result)) ?: false;
    }
}
