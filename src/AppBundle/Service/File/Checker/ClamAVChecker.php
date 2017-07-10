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
    public function __construct(Client $client , LoggerInterface $logger, array $options = [])
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->options = [];
    }

    public function checkFile($body)
    {
        // POST body to clamAV
        try {
            $response = $this->client->post('upload', ['body' => $body]);
            var_dump($response);
        } catch (\Exception $e) {
var_dump($e->getMessage());
        }
exit;
        throw new VirusFoundException('Found virus in file');
    }
}
