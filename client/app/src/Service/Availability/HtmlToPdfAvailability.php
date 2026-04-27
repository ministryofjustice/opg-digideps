<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Availability;

use OPG\Digideps\Frontend\Service\HtmlToPdfGenerator;

class HtmlToPdfAvailability extends ServiceAvailabilityAbstract
{
    private HtmlToPdfGenerator $htmlToPdfGenerator;

    public function __construct(HtmlToPdfGenerator $htmlToPdfGenerator)
    {
        $this->htmlToPdfGenerator = $htmlToPdfGenerator;
    }

    public function ping()
    {
        try {
            $ret = $this->htmlToPdfGenerator->isAlive();
            if (!$ret) {
                throw new \RuntimeException('htmltopdf.isAlive did not return true');
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
        return 'htmlToPdf';
    }
}
