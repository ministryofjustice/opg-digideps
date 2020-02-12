<?php

namespace AppBundle\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SiriusApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(ContainerInterface $container)
    {
        $this->isHealthy = true;

        try {
            $success = false;
            if (!$success) {
                throw new \RuntimeException(sprintf('Returned HTTP code %s', 500));
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
