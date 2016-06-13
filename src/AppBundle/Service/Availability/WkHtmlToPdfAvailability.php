<?php

namespace AppBundle\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

class WkHtmlToPdfAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(ContainerInterface $container)
    {
        try {
            $ret = $container->get('wkhtmltopdf')->isAlive();
            if (!$ret) {
                throw new \RuntimeException('wkhtmltopdf.isAlive did not return true');
            }

            $this->isHealthy = true;
            $this->errors = '';
        } catch (\Exception $e) {
            $this->isHealthy = false;
            $this->errors = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'wkHtmlToPDf';
    }
}
