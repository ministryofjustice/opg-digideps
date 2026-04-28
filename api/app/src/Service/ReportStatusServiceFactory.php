<?php

namespace OPG\Digideps\Backend\Service;

use OPG\Digideps\Backend\Entity\Report\Report;

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
