<?php

namespace App\Service\RestHandler\Report;

use App\Entity\Report\Report;

interface ReportUpdateHandlerInterface
{
    /**
     * @param Report $report
     * @param array $data
     * @return mixed
     */
    public function handle(Report $report, array $data);
}
