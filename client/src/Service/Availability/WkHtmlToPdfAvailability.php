<?php

namespace App\Service\Availability;

use App\Service\WkHtmlToPdfGenerator;

class WkHtmlToPdfAvailability extends ServiceAvailabilityAbstract
{
    /**
     * @var WkHtmlToPdfGenerator
     */
    private WkHtmlToPdfGenerator $wkHtmlToPdfGenerator;

    public function __construct(WkHtmlToPdfGenerator $wkHtmlToPdfGenerator)
    {
        $this->wkHtmlToPdfGenerator = $wkHtmlToPdfGenerator;
    }

    public function ping()
    {
        try {
            $ret = $this->wkHtmlToPdfGenerator->isAlive();
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
