<?php

namespace AppBundle\Service\File\Scanner;

use AppBundle\Service\File\Scanner\Exception\RiskyFileException;
use AppBundle\Service\File\Scanner\Exception\VirusFoundException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response as GuzzlePsr7Response;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileScanner
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScannerEndpointResolver
     */
    private $endpointResolver;

    /**
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param ScannerEndpointResolver $endpointResolver
     */
    public function __construct(ClientInterface $client, LoggerInterface $logger, ScannerEndpointResolver $endpointResolver)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->endpointResolver = $endpointResolver;
    }

    /**
     *
     * Checks file for viruses using ClamAv
     *
     * @param UploadedFile $file
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function scanFile(UploadedFile $file)
    {
        $response = $this->getScanResults($file);
        $isResultPass = strtoupper(trim($response['file_scanner_result'])) === 'PASS';

        $this->logScanResult($file, $isResultPass, $response);

        if ($isResultPass) {
            return true;
        }

        switch (strtoupper(trim($response['file_scanner_code']))) {
            case 'AV_FAIL':
                throw new VirusFoundException();
            case 'PDF_INVALID_FILE':
            case 'PDF_BAD_KEYWORD':
                throw new RiskyFileException();
            default:
                throw new RuntimeException('Files scanner FAIL. Unrecognised code. Full response: ' . print_r($response));
        }
    }

    /**
     * POSTS the file body to file scanner, and continually polls until result is returned.
     *
     * @param UploadedFile $file
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getScanResults(UploadedFile $file)
    {
        // avoid contacting ClamAV for files with already-known asnwer
        if ($cachedResponse = ClamAVMocks::getCachedResponse($file)) {
            return $cachedResponse;
        }

        try {
            $result = $this->makeScannerRequest($file);

            $maxRetries = 90;
            $count = 0;
            $statusResponse = [];

            //TODO use $statusResponse['celery_task_state'] == 'SUCCESS' to verify
            while ((!array_key_exists('file_scanner_result', $statusResponse)) && ($count < $maxRetries)) {
                $statusResponse = $this->makeStatusRequest($result['location']);

                if ($statusResponse === false) {
                    $this->log(Logger::CRITICAL, 'Scanner response could not be decoded');
                    throw new \RunTimeException('Unable to contact file scanner');
                }

                sleep(1);

                $count++;
            }

            if (!array_key_exists('file_scanner_result', $statusResponse)) {
                $this->log(Logger::ERROR, 'Maximum attempts at contacting clamAV for status. Unable to retrieve complete scan result ' . $statusResponse);
            }

            return $statusResponse;
        } catch (\Throwable $e) {
            $this->log(Logger::CRITICAL, 'Scanner exception: ' . $e->getCode() . ' - ' . $e->getMessage());

            throw new \RunTimeException($e);
        }
    }

    /**
     * Send file to File Scanner
     *
     * @param UploadedFile $file
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function makeScannerRequest(UploadedFile $file)
    {
        $fullFilePath = $file->getPathName();

        $scannerEndpoint = $this->endpointResolver->resolve($file);
        $response = $this->client->request('POST', $scannerEndpoint, [
            'multipart' => [
               [
                   'name'=> 'file',
                   'contents' => fopen($fullFilePath, 'r'),
               ]
            ]
        ]);

        if (!$response instanceof GuzzlePsr7Response) {
            throw new \RuntimeException('ClamAV not available');
        }
        $result = json_decode($response->getBody()->getContents(), true);

        return $result;
    }

    /**
     * Query status of file scan using location returned by AV scanner
     * @param string $location
     *
     * @return array
     */
    private function makeStatusRequest($location)
    {
        $this->log(Logger::DEBUG, 'Quering scan status for location: ' . $location);

        $response = $this->client->get($location);
        $result = json_decode($response->getBody()->getContents(), true);

        $this->log(Logger::DEBUG, 'Scan status result for location: ' . $location . ': ');

        return $result;
    }

    /**
     * @param $level
     * @param $message
     * @param UploadedFile|null $file
     * @param array|null        $response
     */
    private function log($level, $message, UploadedFile $file = null, array $response = null)
    {
        $extra = ['service' => 'clam_av_checker'];

        if ($file) {
            $extra['fileName']  = $file->getClientOriginalName();
        }

        if ($response) {
            $extra += [
            'file_scanner_code' => $response['file_scanner_code'],
            'file_scanner_result' => $response['file_scanner_result'],
            'file_scanner_message' => $response['file_scanner_message']
            ];
        }

        $this->logger->log($level, $message, ['extra' => $extra]);
    }

    /**
     * @param UploadedFile $file
     * @param bool $isResultPass
     * @param array $response
     */
    private function logScanResult(UploadedFile $file, bool $isResultPass, array $response): void
    {
        $level = $isResultPass ? Logger::INFO : Logger::ERROR;
        $this->log($level, 'File scan result', $file, $response);
    }
}
