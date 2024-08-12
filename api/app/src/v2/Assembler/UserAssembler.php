<?php

namespace App\v2\Assembler;

use App\Entity\User;
use App\v2\DTO\DtoPropertySetterTrait;
use App\v2\DTO\UserDto;

class UserAssembler
{
    use DtoPropertySetterTrait;

    /** @var ClientAssembler */
    private $clientDtoAssembler;

    public function __construct(?ClientAssembler $clientDtoAssembler = null)
    {
        $this->clientDtoAssembler = $clientDtoAssembler;
    }

    /**
     * @return UserDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new UserDto();

        $this->setPropertiesFromData($dto, $data);

        if ($this->clientDtoAssembler && isset($data['clients']) && is_array($data['clients'])) {
            $dto->setClients($this->assembleUserClients($data['clients']));
        }

        return $dto;
    }

    /**
     * @return UserDto
     */
    public function assembleFromEntity(User $user)
    {
        $dto = new UserDto();

        $dto->setId($user->getId());
        $dto->setFirstName($user->getFirstName());
        $dto->setLastName($user->getLastName());
        $dto->setEmail($user->getEmail());
        $dto->setRoleName($user->getRoleName());
        $dto->setAddressPostcode($user->getAddressPostcode());
        $dto->setNdrEnabled($user->getNdrEnabled());
        $dto->setActive((bool) $user->getActive());
        $dto->setJobTitle($user->getJobTitle());
        $dto->setPhoneMain($user->getPhoneMain());

        return $dto;
    }

    /**
     * @return array
     */
    private function assembleUserClients(array $clients)
    {
        $dtos = [];

        foreach ($clients as $client) {
            $dtos[] = $this->clientDtoAssembler->assembleFromArray($client);
        }

        return $dtos;
    }
}
