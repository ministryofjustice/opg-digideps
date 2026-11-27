<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Assembler;

use OPG\Digideps\Backend\Entity\Deputy;
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

    public function assembleFromEntity(Deputy $deputy): DeputyDto
    {
        $dto = new DeputyDto();

        $this->setPropertiesFromData($dto, [
            'id' => $deputy->getId(),
            'deputyUid' => $deputy->getDeputyUid(),
            'firstName' => $deputy->getFirstname(),
            'lastName' => $deputy->getLastname(),
            'email1' => $deputy->getEmail1(),
            'email2' => $deputy->getEmail2(),
            'email3' => $deputy->getEmail3(),
            'phoneMain' => $deputy->getPhoneMain(),
            'phoneAlterrnative' => $deputy->getPhoneAlternative(),
            'address1' => $deputy->getAddress1(),
            'address2' => $deputy->getAddress2(),
            'address3' => $deputy->getAddress3(),
            'address4' => $deputy->getAddress4(),
            'address5' => $deputy->getAddress5(),
            'addressPostcode' => $deputy->getAddressPostcode(),
            'addressCountry' => $deputy->getAddressCountry(),
        ]);

        return $dto;
    }
}
