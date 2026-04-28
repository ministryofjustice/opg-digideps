<?php

namespace OPG\Digideps\Backend\Service\RestHandler\Report;

use OPG\Digideps\Backend\Entity\Report\Report;

interface ReportUpdateHandlerInterface
{
    /**
     * @return mixed
     */
    public function handle(Report $report, array $data);
}
