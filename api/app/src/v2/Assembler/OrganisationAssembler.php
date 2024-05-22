<?php

namespace App\v2\Assembler;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\User;
use App\v2\DTO\DtoPropertySetterTrait;
use App\v2\DTO\OrganisationDto;

class OrganisationAssembler
{
    use DtoPropertySetterTrait;

    /** @var UserAssembler */
    private $userDtoAssembler;

    /** @var ClientAssembler */
    private $clientDtoAssembler;

    public function __construct(?UserAssembler $userDtoAssembler = null, ?ClientAssembler $clientDtoAssembler = null)
    {
        $this->userDtoAssembler = $userDtoAssembler;
        $this->clientDtoAssembler = $clientDtoAssembler;
    }

    /**
     * @return OrganisationDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new OrganisationDto();

        $this->setPropertiesFromData($dto, $data);

        if (isset($data['users']) && is_array($data['users'])) {
            $dto->setUsers($this->assembleOrganisationUsers($data['users']));
        }

        if (isset($data['clients']) && is_array($data['clients'])) {
            $dto->setClients($this->assembleOrganisationClients($data['clients']));
        }

        return $dto;
    }

    /**
     * @return OrganisationDto
     */
    public function assembleFromEntity(Organisation $organisation)
    {
        $dto = new OrganisationDto();

        $dto->setId($organisation->getId());
        $dto->setName($organisation->getName());
        $dto->setEmailIdentifier($organisation->getEmailIdentifier());
        $dto->setIsActivated($organisation->isActivated());
        $dto->setTotalUserCount($organisation->getTotalUserCount());
        $dto->setTotalClientCount($organisation->getTotalClientCount());

        return $dto;
    }

    /**
     * @return array
     */
    private function assembleOrganisationUsers(iterable $users)
    {
        $dtos = [];

        foreach ($users as $user) {
            if ($user instanceof User) {
                $dtos[] = $this->userDtoAssembler->assembleFromEntity($user);
            } else {
                $dtos[] = $this->userDtoAssembler->assembleFromArray($user);
            }
        }

        return $dtos;
    }

    /**
     * @param array $clients
     *
     * @return array
     */
    private function assembleOrganisationClients(iterable $clients)
    {
        $dtos = [];

        foreach ($clients as $client) {
            if ($client instanceof Client) {
                $dtos[] = $this->clientDtoAssembler->assembleFromEntity($client);
            } else {
                $orgDto = $this->assembleFromArray($client['organisation']);
                $dtos[] = $this->clientDtoAssembler->assembleFromArray($client, $orgDto);
            }
        }

        return $dtos;
    }
}
