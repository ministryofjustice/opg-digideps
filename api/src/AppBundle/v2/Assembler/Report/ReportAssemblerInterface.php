<?php

namespace AppBundle\v2\Assembler\Report;

use AppBundle\v2\DTO\ReportDto;

interface ReportAssemblerInterface
{
    /**
     * @param array $data
     * @return ReportDto
     */
    public function assembleFromArray(array $data);
}
