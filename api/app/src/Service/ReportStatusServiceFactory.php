<?php

namespace OPG\Digideps\Backend\Service;

use OPG\Digideps\Backend\Entity\Report\Report;

class ReportStatusServiceFactory
{
    public function create(Report $report): ReportStatusService
    {
        return new ReportStatusService($report);
    }
}
