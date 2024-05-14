<?php

declare(strict_types=1);

namespace App\v2\Assembler;

use App\Entity\Deputy;
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
        if ($dto->deputyIsAnOrganisation()) {
            $deputyFirstName = $dto->getOrganisationName();
        } else {
            $deputyFirstName = empty($dto->getDeputyFirstname()) ? null : $dto->getDeputyFirstname();
        }

        $namedDeputy = (new Deputy())
            ->setEmail1($dto->getDeputyEmail())
            ->setDeputyUid($dto->getDeputyUid())
            ->setFirstname($deputyFirstName)
            ->setLastname($dto->getDeputyLastname())
            ->setAddress1($dto->getDeputyAddress1())
            ->setAddress2($dto->getDeputyAddress2())
            ->setAddress3($dto->getDeputyAddress3())
            ->setAddress4($dto->getDeputyAddress4())
            ->setAddress5($dto->getDeputyAddress5())
            ->setAddressPostcode($dto->getDeputyPostcode());

        return $namedDeputy;
    }
}
