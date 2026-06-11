<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Assembler;

use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\v2\DTO\DeputyDto;
use OPG\Digideps\Backend\v2\DTO\DtoPropertySetterTrait;
use OPG\Digideps\Backend\v2\Registration\DTO\OrgDeputyshipDto;

class DeputyAssembler
{
    use DtoPropertySetterTrait;

    /**
     * @return DeputyDto
     */
    public function assembleFromArray(array $data): DeputyDto
    {
        $dto = new DeputyDto();

        $this->setPropertiesFromData($dto, $data);

        return $dto;
    }

    public function assembleFromOrgDeputyshipDto(OrgDeputyshipDto $dto, ?Organisation $organisation): Deputy
    {
        if ($dto->deputyIsAnOrganisation()) {
            $deputyFirstName = $dto->getOrganisationName() ?? '';
        } else {
            $deputyFirstName = $dto->getDeputyFirstname() ?? '';
        }

        return new Deputy($dto->getDeputyUid(), DeputyType::from($dto->getDeputyType()), $deputyFirstName, $dto->getDeputyLastname())
            ->setEmail1($dto->getDeputyEmail())
            ->setLastname($dto->getDeputyLastname())
            ->setAddress1($dto->getDeputyAddress1())
            ->setAddress2($dto->getDeputyAddress2())
            ->setAddress3($dto->getDeputyAddress3())
            ->setAddress4($dto->getDeputyAddress4())
            ->setAddress5($dto->getDeputyAddress5())
            ->setAddressPostcode($dto->getDeputyPostcode())
            ->setOrganisation($organisation);
    }
}
