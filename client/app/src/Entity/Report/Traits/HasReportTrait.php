<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

trait HasReportTrait
{
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Report')]
    #[JMS\Groups(['report-object'])]
    private ?Report $report = null;

    #[JMS\VirtualProperty]
    #[JMS\Groups(['report-id'])]
    public function getReportId(): ?int
    {
        return $this->report?->getId();
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

        return $this;
    }
}
