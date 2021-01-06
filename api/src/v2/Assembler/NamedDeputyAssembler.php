<?php

namespace AppBundle\v2\Assembler;

use AppBundle\Entity\NamedDeputy;
use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\Entity\User;
use AppBundle\v2\DTO\NamedDeputyDto;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;

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
