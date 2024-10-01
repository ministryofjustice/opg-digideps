<?php

namespace App\Service\Availability;

use App\Service\Client\Sirius\SiriusApiGatewayClient;

class SiriusApiAvailability extends ServiceAvailabilityAbstract
{
    private SiriusApiGatewayClient $client;

    public function __construct(SiriusApiGatewayClient $client)
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
                $this->errors = 'Returned HTTP code '.$response->getStatusCode();
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
