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

    /**
     * @param NamedDeputy $namedDeputy
     * @return NamedDeputyDto
     */
    public function assembleFromEntity(NamedDeputy $namedDeputy)
    {
        $dto = new NamedDeputyDto();

        $dto->setId($namedDeputy->getId());
        $dto->setDeputyNo($namedDeputy->getDeputyNo());
        $dto->setFirstName($namedDeputy->getFirstName());
        $dto->setLastName($namedDeputy->getLastName());
        $dto->setEmail1($namedDeputy->getEmail1());
        $dto->setEmail2($namedDeputy->getEmail2());
        $dto->setEmail3($namedDeputy->getEmail3());
        $dto->setDepAddrNo($namedDeputy->getDepAddrNo());
        $dto->setPhoneMain($namedDeputy->getPhoneMain());
        $dto->setPhoneAlterrnative($namedDeputy->getPhoneAlternative());
        $dto->setAddress1($namedDeputy->getAddress1());
        $dto->setAddress2($namedDeputy->getAddress2());
        $dto->setAddress3($namedDeputy->getAddress3());
        $dto->setAddress4($namedDeputy->getAddress4());
        $dto->setAddress5($namedDeputy->getAddress5());
        $dto->setAddressPostcode($namedDeputy->getAddressPostcode());
        $dto->setAddressCountry($namedDeputy->getAddressCountry());

        return $dto;
    }
}
