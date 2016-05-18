<?php

namespace AppBundle\Entity\Traits;

use JMS\Serializer\Annotation as JMS;
use AppBundle\Entity\Report;

trait HasReportTrait
{
    /**
     * @JMS\Type("AppBundle\Entity\Report")
     */
    private $report;

    /**
     * @JMS\VirtualProperty
     * 
     * @return int
     */
    public function getReportId()
    {
        return $this->report ? $this->report->getId() : null;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }
}
