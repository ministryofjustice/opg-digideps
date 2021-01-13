<?php

namespace App\Service\Availability;

use App\Service\WkHtmlToPdfGenerator;

class WkHtmlToPdfAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(WkHtmlToPdfGenerator $wkHtmlToPdfGenerator)
    {
        try {
            $ret = $wkHtmlToPdfGenerator->isAlive();
            if (!$ret) {
                throw new \RuntimeException('wkhtmltopdf.isAlive did not return true');
            }

            $this->isHealthy = true;
            $this->errors = '';
        } catch (\Throwable $e) {
            $this->isHealthy = false;
            $this->errors = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'wkHtmlToPDf';
    }
}
