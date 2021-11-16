<?php

namespace App\Service\Availability;

use App\Service\Client\Sirius\SiriusApiGatewayClient;

class SiriusApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(private SiriusApiGatewayClient $client)
    {
        $this->isHealthy = true;
    }

    public function ping()
    {
        try {
            $response = $this->client->get('healthcheck');

            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException('returned HTTP code ' . $response->getStatusCode());
            }
        } catch (\Throwable $e) {
            $this->customMessage = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Sirius';
    }
}
