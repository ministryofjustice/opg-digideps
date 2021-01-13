<?php

namespace App\Service\Availability;

use GuzzleHttp\ClientInterface;

/**
 * Check if the Clam AV antivirus is running, using the PING enpoint
 * https://github.com/ministryofjustice/opg-file-scanner-service
 */
class ClamAvAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(ClientInterface $fileScannerClient)
    {
        try {
            $response = $fileScannerClient->get('/');
            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException('returned HTTP code ' . $response->getStatusCode());
            }

            $this->isHealthy = true;
        } catch (\Throwable $e) {
            $this->isHealthy = false;
            $this->errors = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'ClamAV';
    }
}
