<?php

namespace AppBundle\v2\Assembler;

use AppBundle\Entity\NamedDeputy;
use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\Entity\User;
use AppBundle\v2\DTO\NamedDeputyDto;

class NamedDeputyAssembler
{
    use DtoPropertySetterTrait;

    /**
     * @param array $data
     * @return NamedDeputyDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new NamedDeputyDto();

        $this->setPropertiesFromData($dto, $data);

        return $dto;
    }
}
