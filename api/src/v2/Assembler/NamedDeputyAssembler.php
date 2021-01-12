<?php

namespace App\v2\Assembler;

use App\Entity\NamedDeputy;
use App\v2\DTO\DtoPropertySetterTrait;
use App\Entity\User;
use App\v2\DTO\NamedDeputyDto;
use App\v2\Registration\DTO\OrgDeputyshipDto;

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

    public function assembleFromOrgDeputyshipDto(OrgDeputyshipDto $dto)
    {
        return (new NamedDeputy())
            ->setEmail1($dto->getDeputyEmail())
            ->setDeputyNo($dto->getDeputyNumber())
            ->setFirstname($dto->getDeputyFirstname() ? $dto->getDeputyFirstname() : null)
            ->setLastname($dto->getDeputyLastname())
            ->setAddress1($dto->getDeputyAddress1())
            ->setAddressPostcode($dto->getDeputyPostcode());
    }
}
