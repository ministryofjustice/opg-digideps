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
            $data =json_decode($response->getBody(), true);
            if (!$data) {
                throw new \RuntimeException(json_last_error_msg());
            }
            if (!array_key_exists('pid', array_shift($data))) {
                throw new \RuntimeException('worker pid not found');
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
