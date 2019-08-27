<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\v2\DTO\OrganisationDto;

class OrganisationAssembler
{
    use DtoPropertySetterTrait;

    /** @var DeputyAssembler  */
    private $deputyDtoAssembler;

    /**
     * @param DeputyAssembler $deputyDtoAssembler
     */
    public function __construct(DeputyAssembler $deputyDtoAssembler = null)
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
}
