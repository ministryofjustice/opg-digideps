<?php

namespace AppBundle\Service\Availability;

use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;

class SiriusApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(SiriusApiGatewayClient $client)
    {
        $this->isHealthy = true;

        try {
            $response = $client->get('v1/healthcheck');

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
