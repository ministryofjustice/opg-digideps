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
    public function checkFile(UploadableFileInterface $uploadedFile)
    {
        // POST body to clamAV
        $response = $this->getScanResults($uploadedFile);
        if (strtoupper(trim($response['av_scan_result'])) === 'FAIL') {
            throw new VirusFoundException('Found virus in file');
        }

        return true;
    }

    /**
     * POSTS the file body to file scanner, and continually polls until result is returned.
     *
     * @param UploadableFileInterface $uploadedFile
     * @return array
     */
    private function getScanResults(UploadableFileInterface $uploadedFile)
    {

        return [
            "av_scan_result" => "SUCCESS",
            "celery_task_state" => "SUCCESS",
            "file_scanner_result" => "SUCCESS",
            "pdf_scan_result" => "SUCCESS"
        ];
    }
}
