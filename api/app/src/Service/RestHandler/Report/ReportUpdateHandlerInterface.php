<?php

namespace App\Service\RestHandler\Report;

use App\Entity\Report\Report;

interface ReportUpdateHandlerInterface
{
    /**
     * @return mixed
     */
    public function handle(Report $report, array $data);
}
