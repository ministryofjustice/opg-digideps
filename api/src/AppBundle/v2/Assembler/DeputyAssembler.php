<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\Entity\User;

class DeputyAssembler
{
    use DtoPropertySetterTrait;

    /**
     * @param array $data
     * @return DeputyDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new DeputyDto();

        $this->setPropertiesFromData($dto, $data);

        return $dto;
    }

    /**
     * @param User $deputy
     * @return DeputyDto
     */
    public function assembleFromEntity(User $deputy)
    {
        $dto = new DeputyDto();

        $dto->setId($deputy->getId());
        $dto->setFirstName($deputy->getFirstName());
        $dto->setLastName($deputy->getLastName());
        $dto->setEmail($deputy->getEmail());
        $dto->setRoleName($deputy->getRoleName());
        $dto->setAddressPostcode($deputy->getAddressPostcode());
        $dto->setNdrEnabled($deputy->getNdrEnabled());
        $dto->setActive((bool) $deputy->getActive());
        $dto->setJobTitle($deputy->getJobTitle());
        $dto->setPhoneMain($deputy->getPhoneMain());

        return $dto;
    }
}
