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

    /** @var ClientAssembler  */
    private $clientDtoAssembler;

    /**
     * @param DeputyAssembler $deputyDtoAssembler
     * @param ClientAssembler $clientDtoAssembler
     */
    public function __construct(DeputyAssembler $deputyDtoAssembler = null, ClientAssembler $clientDtoAssembler = null)
    {
        $this->deputyDtoAssembler = $deputyDtoAssembler;
        $this->clientDtoAssembler = $clientDtoAssembler;
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

        if (isset($data['clients'])  && is_array($data['clients'])) {
            $dto->setClients($this->assembleOrganisationClients($data['clients']));
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

    /**
     * @param array $clients
     * @return array
     */
    private function assembleOrganisationClients(array $clients)
    {
        $dtos = [];

        foreach ($clients as $client) {
            $dtos[] = $this->clientDtoAssembler->assembleFromArray($client);
        }

        return $dtos;
    }
}
