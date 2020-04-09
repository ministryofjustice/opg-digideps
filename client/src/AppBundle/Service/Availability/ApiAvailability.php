<?php

namespace AppBundle\Service\Availability;

use AppBundle\Service\Client\RestClient;

class ApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(RestClient $restClient)
    {
        try {
            $data = $restClient->get('manage/availability', 'array');
            // API not healtyh
            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['healthy'])) {
                $this->isHealthy = false;
                $this->errors = 'Cannot read API status. ' . json_last_error_msg();

                return;
            }

            // API healthy
            $this->isHealthy = $data['healthy'];
            $this->errors = $data['errors'];
        } catch (\Throwable $e) {
            $this->isHealthy = false;
            $this->errors = 'Error when using RestClient to connect to API . ' . $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Api';
    }
}
