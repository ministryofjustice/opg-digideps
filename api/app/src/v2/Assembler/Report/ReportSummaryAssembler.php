<?php

namespace App\v2\Assembler\Report;

use App\v2\DTO\DtoPropertySetterTrait;
use App\v2\DTO\ReportDto;

class ReportSummaryAssembler implements ReportAssemblerInterface
{
    use DtoPropertySetterTrait;

    /**
     * @return ReportDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new ReportDto();

        $this->setPropertiesFromData($dto, $data);

        return $dto;
    }
}
