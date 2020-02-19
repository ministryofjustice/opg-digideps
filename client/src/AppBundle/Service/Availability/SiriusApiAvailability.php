<?php

namespace AppBundle\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SiriusApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(ContainerInterface $container)
    {
        $this->isHealthy = true;

        try {
            $response = $container->get('AppBundle\Service\Client\Sirius\SiriusApiGatewayClient')->get('v1/healthcheck');

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
