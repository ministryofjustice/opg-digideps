<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Assembler;

use OPG\Digideps\Backend\v2\DTO\DeputyDto;
use OPG\Digideps\Backend\v2\DTO\DtoPropertySetterTrait;

class DeputyAssembler
{
    use DtoPropertySetterTrait;

    public function assembleFromArray(array $data): DeputyDto
    {
        $dto = new DeputyDto();

        $this->setPropertiesFromData($dto, $data);

        return $dto;
    }
}
