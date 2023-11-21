<?php

namespace App\v2\Assembler;

use App\Entity\User;
use App\v2\DTO\DeputyDto;
use App\v2\DTO\DtoPropertySetterTrait;

class DeputyAssembler
{
    use DtoPropertySetterTrait;

    /** @var ClientAssembler */
    private $clientDtoAssembler;

    /**
     * @param ClientAssembler $clientDtoAssembler
     */
    public function __construct(ClientAssembler $clientDtoAssembler = null)
    {
        $this->clientDtoAssembler = $clientDtoAssembler;
    }

    /**
     * @return DeputyDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new DeputyDto();

        $this->setPropertiesFromData($dto, $data);

        if ($this->clientDtoAssembler && isset($data['clients']) && is_array($data['clients'])) {
            $dto->setClients($this->assembleDeputyClients($data['clients']));
        }

        return $dto;
    }

    /**
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
}
