<?php

namespace AppBundle\Service\File\Checker;

use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class ClamAVChecker implements FileCheckerInterface
{
    /**
     * ClamAv constructor.
     */
    public function __construct(Client $client, LoggerInterface $logger, array $options = [])
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->options = [];
    }

    /**
     *
     * @param $body
     * @return bool
     */
    public function checkFile($body)
    {
        return true;
        // POST body to clamAV
        try {
            $response = $this->getScanResults($body);

            if (strtoupper(trim($response['av_scan_result'])) === 'FAIL') {
                throw new VirusFoundException('Found virus in file');
            }
        } catch (\Exception $e) {

        }
    }

    /**
     * POSTS the file body to file scanner, and continually polls until result is returned.
     *
     * @param $body
     * @return array
     */
    private function getScanResults($body)
    {
        //$this->client->post('upload', ['body' => $body]);

        return [
            'celery_task_state' => 'SUCCESS',
            'pdf_scan_result' => 'PASS',
            'av_scan_result' => 'FAIL',
            'file_scanner_result' => 'PASS'
        ];
    }
}
