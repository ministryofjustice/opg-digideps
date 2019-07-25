<?php

namespace AppBundle\Service\RestHandler\Report;

use AppBundle\Entity\Report\Report;

interface ReportUpdateHandlerInterface
{
    /**
     * @param Report $report
     * @param array $data
     * @return mixed
     */
    public function handle(Report $report, array $data);
}
