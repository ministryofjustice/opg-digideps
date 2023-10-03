<?php

namespace App\Service\Availability;

use App\Service\Client\RestClient;

class ApiAvailability extends ServiceAvailabilityAbstract
{
    private RestClient $restClient;

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    public function ping()
    {
        try {
            $data = $this->restClient->get('health-check/service', 'array');
            // API not healtyh
            if (JSON_ERROR_NONE !== json_last_error() || !isset($data['healthy'])) {
                $this->isHealthy = false;
                $this->errors = 'Cannot read API status. '.json_last_error_msg();

                return;
            }

            // API healthy
            $this->isHealthy = $data['healthy'];
            $this->errors = $data['errors'];
        } catch (\Throwable $e) {
            $this->isHealthy = false;
            $this->errors = 'Error when using RestClient to connect to API . '.$e->getMessage();
        }
    }

    public function getName()
    {
        return 'Api';
    }
}
