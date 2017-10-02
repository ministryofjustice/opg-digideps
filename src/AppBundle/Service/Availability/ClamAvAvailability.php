<?php

namespace AppBundle\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if the Clam AV antivirus is running, using the PING enpoint
 * https://github.com/ministryofjustice/opg-file-scanner-service
 */
class ClamAvAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(ContainerInterface $container)
    {
        try {
            $response = $container->get('guzzle_file_scanner_client')->get('/ping/json');
            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException("returned HTTP code " . $response->getStatusCode());
            }

            $this->isHealthy = true;
        } catch (\Exception $e) {
            $this->isHealthy = false;
            $this->errors = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'ClamAV';
    }
}
