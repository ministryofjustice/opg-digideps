<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\InvalidFileException;
use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Types\UploadableFileInterface;
use AppBundle\Service\File\Types\Pdf;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use GuzzleHttp\Post\PostFile;

class ClamAVChecker implements FileCheckerInterface
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
     * @var array
     */
    private $options;

    /**
     * ClamAv constructor.
     */
    public function __construct(ClientInterface $client, LoggerInterface $logger, array $options = [])
    {
        /** @var GuzzleHttp\Client client */
        $this->client = $client;
        $this->logger = $logger;
        $this->options = [];
    }

    /**
     *
     * Checks file for viruses using ClamAv
     *
     * @param UploadableFileInterface $uploadedFile
     *
     * @throws RuntimeException in case the result is not PASS
     *
     * @return bool
     */
    public function checkFile(UploadableFileInterface $file)
    {
        // POST body to clamAV
        $response = $this->getScanResults($file);

        $file->setScanResult($response);
        $fileName = $file->getUploadedFile()->getClientOriginalName();

        $fileScannerResult = strtoupper(trim($response['file_scanner_result']));
        $fileScannerCode = strtoupper(trim($response['file_scanner_code']));
        $fileScannerMessage = strtoupper(trim($response['file_scanner_message']));

            if ($file instanceof Pdf && $response['pdf_scan_result'] !== 'PASS') {
                $this->logger->warning('PDF file scan result failed in ' . $file->getUploadedFile()->getClientOriginalName() .
                    ' - ' . $file->getUploadedFile()->getPathName() . '. Scan Result: ' . json_encode($response));
                throw new RiskyFileException('PDF file scan failed');
            }

            $this->logger->info('File scan passed for ' . $file->getUploadedFile()->getClientOriginalName() .
                ' - ' . $file->getUploadedFile()->getPathName() . '. Scan Result: ' . json_encode($response));
        if ($fileScannerResult === 'PASS') {
            $this->logger->warning("Scan result of $fileName: PASS");
            return true;
        }

        $this->logger->warning("Scan result of $fileName: $fileScannerResult, $fileScannerMessage (code $fileScannerCode)");

        switch($fileScannerCode) {
            case 'AV_FAIL':
                throw new VirusFoundException('Found virus in file');
            case 'PDF_INVALID_FILE':
            case 'PDF_BAD_KEYWORD':
                throw new RiskyFileException('Invalid PDF');
        }

        throw new RuntimeException($fileScannerMessage);
    }

    /**
     * POSTS the file body to file scanner, and continually polls until result is returned.
     *
     * @param UploadableFileInterface $uploadedFile
     * @return array
     */
    private function getScanResults(UploadableFileInterface $file)
    {
        try {
            $result = $this->makeScannerRequest($file);

            $maxRetries = 90;
            $count = 0;
            $statusResponse = [];

            //TODO use $statusResponse['celery_task_state'] == 'SUCCESS' to verify
            while ((!array_key_exists('file_scanner_result', $statusResponse)) && ($count < $maxRetries))
            {
                $statusResponse = $this->makeStatusRequest($result['location']);

                if ($statusResponse === false) {
                    $this->logger->critical('Scanner response could not be decoded');
                    throw new \RunTimeException('Unable to contact file scanner');
                }

                sleep(1);

                $count++;
            }

            if (!array_key_exists('file_scanner_result', $statusResponse)) {
                $this->logger->warning('Maximum attempts at contacting clamAV for status. Unable to retrieve complete scan result ' . $statusResponse);
            }

            return $statusResponse;

        } catch (\Exception $e) {
            $this->logger->critical('Scanner exception: ' . $e->getCode() . ' - ' . $e->getMessage());

            throw new \RunTimeException($e);
        }
    }

    /**
     * Send file to File Scanner
     *
     * @param UploadableFileInterface $file
     *
     * @return array
     */
    private function makeScannerRequest(UploadableFileInterface $file)
    {
        $fullFilePath = $file->getUploadedFile()->getPathName();

        $this->logger->debug('Sending file: ' . $fullFilePath . '  to scanner');

        $request = $this->client->createRequest('POST', 'upload');
        $postBody = $request->getBody();
        $postBody->addFile(
            new PostFile('file', fopen($fullFilePath, 'r'))
        );

        $response = $this->client->send($request);
        if (!$response instanceof ResponseInterface ) {
            throw new \RuntimeException('ClamAV not available');
        }
        $result = json_decode($response->getBody()->getContents(), true);

        $this->logger->debug('Scanner send result: ' . json_encode($result));

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
        $this->logger->debug('Quering scan status for location: ' . $location);

        $response = $this->client->get($location);
        $result = json_decode($response->getBody()->getContents(), true);

        $this->logger->debug('Scan status result for location: ' . $location . ': ' . json_encode($result));

        return $result;
    }
}
