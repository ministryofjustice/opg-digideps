<?php

namespace OPG\Digideps\Backend\Service\RestHandler\Report;

use OPG\Digideps\Backend\Entity\Report\Report;

interface ReportUpdateHandlerInterface
{
    public function handle(Report $report, array $data): void;
}
