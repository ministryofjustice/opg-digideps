<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Types\UploadableFileInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use GuzzleHttp\Post\PostFile;

class ClamAVChecker implements FileCheckerInterface
{
    /**
     * ClamAv constructor.
     */
    public function __construct(Client $client, LoggerInterface $logger, array $options = [])
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
     * @return bool
     */
    public function checkFile(UploadableFileInterface $file)
    {
        // POST body to clamAV
        $response = $this->getScanResults($file);
        
        return true;
    }

    /**
     * POSTS the file body to file scanner, and continually polls until result is returned.
     *
     * @param UploadableFileInterface $uploadedFile
     * @return array
     */
    private function getScanResults(UploadableFileInterface $file)
    {
        $fileContent = file_get_contents($file->getUploadedFile()->getPathName());

        try {
            $result = $this->makeScannerRequest($file);

            $maxRetries = 10;
            $count = 0;
            $statusResponse = [];

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
                $this->logger->warning('Unable to retrieve complete scan result ' . $statusResponse);
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
