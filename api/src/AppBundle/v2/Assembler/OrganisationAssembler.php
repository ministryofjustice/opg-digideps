<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\v2\DTO\OrganisationDto;

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
     * @param array $users
     * @return array
     */
    private function assembleOrganisationUsers(array $users)
    {
        $dtos = [];

        foreach ($users as $user) {
            $dtos[] = $this->deputyDtoAssembler->assembleFromArray($user);
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
