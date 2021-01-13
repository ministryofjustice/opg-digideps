<?php

namespace App\Service;

use App\Entity\Report\Report;

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
