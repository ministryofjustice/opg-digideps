<?php

namespace App\v2\Assembler;

use App\Entity\NamedDeputy;
use App\v2\DTO\DtoPropertySetterTrait;
use App\v2\DTO\NamedDeputyDto;
use App\v2\Registration\DTO\OrgDeputyshipDto;

class NamedDeputyAssembler
{
    use DtoPropertySetterTrait;

    /**
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
            ->setDeputyNo(sprintf('%s-%s', $dto->getDeputyNumber(), $dto->getDeputyAddressNumber()))
            ->setFirstname($dto->getDeputyFirstname() ?: null)
            ->setLastname($dto->getDeputyLastname())
            ->setAddress1($dto->getDeputyAddress1())
            ->setAddress2($dto->getDeputyAddress2())
            ->setAddress3($dto->getDeputyAddress3())
            ->setAddress4($dto->getDeputyAddress4())
            ->setAddress5($dto->getDeputyAddress5())
            ->setAddressPostcode($dto->getDeputyPostcode());
    }
}
