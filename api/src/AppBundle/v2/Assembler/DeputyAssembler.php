<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\Entity\User;

class DeputyAssembler
{
    use DtoPropertySetterTrait;

    /** @var ClientAssembler  */
    private $clientDtoAssembler;

    /** @var OrganisationAssembler */
    private $organisationDtoAssembler;

    /**
     * @param ClientAssembler $clientDtoAssembler
     * @param OrganisationAssembler $organisationDtoAssembler
     */
    public function __construct(ClientAssembler $clientDtoAssembler, OrganisationAssembler $organisationDtoAssembler)
    {
        $this->clientDtoAssembler = $clientDtoAssembler;
        $this->organisationDtoAssembler = $organisationDtoAssembler;
    }

    /**
     * @param array $data
     * @return DeputyDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new DeputyDto();

        $this->setPropertiesFromData($dto, $data);

        if (isset($data['clients']) && is_array($data['clients']) && isset($data['clients'][0])) {
            $dto->setClients($this->assembleDeputyClients($data['clients']));
        }

        if (isset($data['organisations']) && is_array($data['organisations']) && isset($data['organisations'][0])) {
            $dto->setOrganisation($this->assembleDeputyOrganisation($data['organisations'][0]));
        }

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

    /**
     * @param array $clients
     * @return array
     */
    private function assembleDeputyClients(array $clients)
    {
        $dtos = [];

        foreach ($clients as $client) {
            $dtos[] = $this->clientDtoAssembler->assembleFromArray($client);
        }

        return $dtos;
    }

    /**
     * @param array $organisation
     * @return OrganisationDto
     */
    private function assembleDeputyOrganisation(array $organisation)
    {
        return $this->organisationDtoAssembler->assembleFromArray($organisation);
    }
}
