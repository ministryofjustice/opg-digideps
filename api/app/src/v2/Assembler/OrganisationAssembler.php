<?php

namespace OPG\Digideps\Backend\v2\Assembler;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\v2\DTO\DtoPropertySetterTrait;
use OPG\Digideps\Backend\v2\DTO\OrganisationDto;

class OrganisationAssembler
{
    use DtoPropertySetterTrait;

    public function __construct(
        private readonly UserAssembler $userDtoAssembler,
        private readonly ClientAssembler $clientDtoAssembler
    ) {}

    /**
     * @return OrganisationDto
     */
    public function assembleFromArray(array $data): OrganisationDto
    {
        $dto = new OrganisationDto();

        $this->setPropertiesFromData($dto, $data);

        if (isset($data['users']) && is_array($data['users'])) {
            /** @var User[] $users */
            $users = $this->assembleOrganisationUsers($data['users']);
            $dto->setUsers($users);
        }

        if (isset($data['clients']) && is_array($data['clients'])) {
            /** @var Client[] $clients */
            $clients = $this->assembleOrganisationClients($data['clients']);
            $dto->setClients($clients);
        }

        return $dto;
    }

    /**
     * @return OrganisationDto
     */
    public function assembleFromEntity(Organisation $organisation): OrganisationDto
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
    private function assembleOrganisationUsers(iterable $users): array
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
    private function assembleOrganisationClients(iterable $clients): array
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
