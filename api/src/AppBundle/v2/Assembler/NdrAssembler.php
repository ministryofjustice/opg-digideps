<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\v2\DTO\NdrDto;

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
