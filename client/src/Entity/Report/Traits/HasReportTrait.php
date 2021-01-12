<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

trait HasReportTrait
{
    /**
     * @JMS\Type("App\Entity\Report\Report")
     * @JMS\Groups({"report-object"})
     */
    private $report;

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"report-id"})
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
