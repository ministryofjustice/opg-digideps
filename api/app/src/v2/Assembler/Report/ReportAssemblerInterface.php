<?php

namespace OPG\Digideps\Backend\v2\Assembler\Report;

use OPG\Digideps\Backend\v2\DTO\ReportDto;

interface ReportAssemblerInterface
{
    public function assembleFromArray(array $data): ReportDto;
}
