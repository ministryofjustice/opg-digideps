<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DeputyDto;

class DeputyAssembler
{
    /** @var ClientAssembler  */
    private $clientDtoAssembler;

    /** @var DeputyDto */
    private $deputyDto;

    /**
     * @param ClientAssembler $clientDtoAssembler
     */
    public function __construct(ClientAssembler $clientDtoAssembler)
    {
        $this->clientDtoAssembler = $clientDtoAssembler;
    }

    /**
     * @param array $data
     * @return DeputyDto
     */
    public function assembleFromArray(array $data)
    {
        return $this
            ->throwExceptionIfMissingRequiredData($data)
            ->buildDeputyDtoFromArray($data)
            ->buildAndAttachClientDtosFromArray($data['clients'])
            ->getDeputyDto();
    }

    /**
     * @param array $data
     * @return DeputyAssembler
     */
    private function throwExceptionIfMissingRequiredData(array $data)
    {
        if (!$this->dataIsValid($data)) {
            throw new \InvalidArgumentException(__CLASS__ . ': Missing all data required to build DTO');
        }

        return $this;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function dataIsValid(array $data)
    {
        return
            array_key_exists('id', $data) &&
            array_key_exists('firstname', $data) &&
            array_key_exists('lastname', $data) &&
            array_key_exists('email', $data) &&
            array_key_exists('role_name', $data) &&
            array_key_exists('address_postcode', $data) &&
            array_key_exists('odr_enabled', $data) &&
            array_key_exists('clients', $data);
    }

    /**
     * @param $deputy
     * @return DeputyAssembler
     */
    private function buildDeputyDtoFromArray($deputy)
    {
        $this->deputyDto = new DeputyDto(
            $deputy['id'],
            $deputy['firstname'],
            $deputy['lastname'],
            $deputy['email'],
            $deputy['role_name'],
            $deputy['address_postcode'],
            $deputy['odr_enabled']
        );

        return $this;
    }

    /**
     * @param array $clients
     * @return DeputyAssembler
     */
    private function buildAndAttachClientDtosFromArray(array $clients)
    {
        $clients =  array_map(function ($client) {
            return $this->clientDtoAssembler->assembleFromArray($client);
        }, $clients);

        $this->deputyDto->setClients($clients);

        return $this;
    }

    /**
     * @return DeputyDto
     */
    private function getDeputyDto()
    {
        return $this->deputyDto;
    }
}
