<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\v2\DTO\OrganisationDto;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;

class OrganisationAssembler
{
    use DtoPropertySetterTrait;

    /** @var DeputyAssembler  */
    private $deputyDtoAssembler;

    /**
     * @param DeputyAssembler $deputyDtoAssembler
     */
    public function __construct(DeputyAssembler $deputyDtoAssembler)
    {
        $this->deputyDtoAssembler = $deputyDtoAssembler;
    }

    /**
     * @param array $data
     * @return OrganisationDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new OrganisationDto();

        $this->setPropertiesFromData($dto, $data);

        if (isset($data['users'])  && is_array($data['users'])) {
            $dto->setUsers($this->assembleOrganisationUsers($data['users']));
        }

        return $dto;
    }

    /**
     * @param Organisation $organisation
     * @return OrganisationDto
     */
    public function assembleFromEntity(Organisation $organisation)
    {
        $dto = new OrganisationDto();

        $dto->setId($organisation->getId());
        $dto->setName($organisation->getName());
        $dto->setEmailIdentifier($organisation->getEmailIdentifier());
        $dto->setIsActivated($organisation->isActivated());

        if ($organisation->getUsers()) {
            $dto->setUsers($this->assembleOrganisationUsers($organisation->getUsers()));
        }

        return $dto;
    }

    /**
     * @param iterable $users
     * @return array
     */
    private function assembleOrganisationUsers(iterable $users)
    {
        $dtos = [];

        foreach ($users as $user) {
            if ($user instanceof User) {
                $dtos[] = $this->deputyDtoAssembler->assembleFromEntity($user);
            } else {
                $dtos[] = $this->deputyDtoAssembler->assembleFromArray($user);
            }
        }

        return $dtos;
    }
}
