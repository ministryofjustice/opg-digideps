<?php

namespace App\v2\Assembler;

use App\v2\DTO\DtoPropertySetterTrait;
use App\v2\DTO\NdrDto;

class NdrAssembler
{
    use DtoPropertySetterTrait;

    /**
     * @param array $data
     * @return NdrDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new NdrDto();

        $this->setPropertiesFromData($dto, $data);

        return $dto;
    }
}
