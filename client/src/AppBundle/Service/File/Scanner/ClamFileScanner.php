<?php

namespace AppBundle\Service\File\Scanner;

use AppBundle\Service\File\Scanner\Exception\VirusFoundException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
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
    const MAX_SCAN_ATTEMPTS = 90;

    /**
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param array $badPdfKeywords
     */
    public function __construct(ClientInterface $client, LoggerInterface $logger, array $badPdfKeywords)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->badPdfKeywords = $badPdfKeywords;
    }

    /**
     * @param UploadedFile $file
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function scanFile(UploadedFile $file): bool
    {
        if ($this->fileIsPdf($file) && $this->pdfContainsBadKeywords($file)) {
            $this->logger->info(sprintf('Scan result: bad keyword found in file: %s', $file->getClientOriginalName()));
            throw new VirusFoundException();
        }

        $attempts = 0;
        while ($attempts < self::MAX_SCAN_ATTEMPTS) {
            try {
                $attempts += 1;
                $response = $this->attemptScan($file);
                break;
            }
            catch (ServerException $e) {}
            catch (ConnectException $e) {}
        }

        if (!$response instanceof Response) {
            $this->logger->critical(sprintf('Scanner service down: %s', $e->getMessage()));
            throw new \RuntimeException('Scanner service not available');
        }

        if (!$this->scanResultIsPass($response)) {
            $this->logger->info(sprintf('Scan result: virus found in file: %s', $file->getClientOriginalName()));
            throw new VirusFoundException();
        }

        return true;
    }

    /**
     * @param UploadedFile $file
     * @return bool
     */
    private function fileIsPdf(UploadedFile $file): bool
    {
        return 'pdf' === strtolower($file->getClientOriginalExtension());
    }

    /**
     * @param UploadedFile $file
     * @return bool
     */
    private function pdfContainsBadKeywords(UploadedFile $file): bool
    {
        $regex = sprintf('/<<\s*\/(%s)/', implode('|', $this->badPdfKeywords));
        $contents = file_get_contents($file->getPathname());

        return (bool) preg_match($regex, $contents);
    }

    /**
     * @param UploadedFile $file
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function attemptScan(UploadedFile $file): ResponseInterface
    {
        return $this
            ->client
            ->request('POST', "/scan?name={$file->getClientOriginalName()}", [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($file->getPathName(), 'r'),
                ]
            ]
        ]);
    }

    /**
     * @param Response $response
     * @return bool
     */
    private function scanResultIsPass(Response $response): bool
    {
        $result = explode(':', trim($response->getBody()->getContents()));

        return trim(end($result)) === 'true' ?: false;
    }
}
