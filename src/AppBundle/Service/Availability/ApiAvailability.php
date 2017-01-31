<?php

namespace AppBundle\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(ContainerInterface $container)
    {
        try {
            $data = $container->get('rest_client')->get('manage/availability', 'array');
            // API not healtyh
            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['healthy'])) {
                $this->isHealthy = false;
                $this->errors = 'Cannot read API status. ' . json_last_error_msg();

                return;
            }

            // API healthy
            $this->isHealthy = $data['healthy'];
            $this->errors = $data['errors'];
        } catch (\Exception $e) {
            $this->isHealthy = false;
            $this->errors = 'Error when using RestClient to connect to API . ' . $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Api';
    }
}
