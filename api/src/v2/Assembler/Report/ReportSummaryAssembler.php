<?php

namespace AppBundle\v2\Assembler\Report;

use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\v2\DTO\ReportDto;

class ReportSummaryAssembler implements ReportAssemblerInterface
{
    use DtoPropertySetterTrait;

    /**
     * @param array $data
     * @return ReportDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new ReportDto();

        $this->setPropertiesFromData($dto, $data);

        return $dto;
    }
}
