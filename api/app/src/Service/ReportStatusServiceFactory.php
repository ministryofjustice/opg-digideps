<?php

namespace App\Service;

use App\Entity\Report\Report;

class ReportStatusServiceFactory
{
    /**
     * @return ReportStatusService
     */
    public function create(Report $report)
    {
        return new ReportStatusService($report);
    }
}
