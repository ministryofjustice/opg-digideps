<?php

namespace OPG\Digideps\Frontend\Service\Availability;

use OPG\Digideps\Frontend\Service\Client\Sirius\SiriusHealthCheckClient;

class SiriusApiAvailability extends ServiceAvailabilityAbstract
{
    private SiriusHealthCheckClient $client;

    public function __construct(SiriusHealthCheckClient $client)
    {
        $this->isHealthy = true;
        $this->client = $client;
    }

    public function ping()
    {
        try {
            $response = $this->client->get('healthcheck');

            if (200 !== $response->getStatusCode()) {
                $this->isHealthy = false;
                $this->errors = 'Returned HTTP code ' . $response->getStatusCode();
            }
        } catch (\Throwable $e) {
            $this->isHealthy = false;
            $this->errors = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Sirius';
    }
}
