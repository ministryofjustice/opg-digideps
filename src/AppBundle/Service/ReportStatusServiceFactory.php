<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;

class ReportStatusServiceFactory
{
    /**
     * @param Report $report
     * @return ReportStatusService
     */
    public function create(Report $report)
    {
        return new ReportStatusService($report);
    }
}
