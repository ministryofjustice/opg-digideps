<?php

namespace OPG\Digideps\Backend\v2\Assembler\Report;

use OPG\Digideps\Backend\v2\DTO\DtoPropertySetterTrait;
use OPG\Digideps\Backend\v2\DTO\ReportDto;

class ReportSummaryAssembler implements ReportAssemblerInterface
{
    use DtoPropertySetterTrait;

    public function assembleFromArray(array $data): ReportDto
    {
        $dto = new ReportDto();

        $this->setPropertiesFromData($dto, $data);

        return $dto;
    }
}
