<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DeputyDto;

class LayDeputyAssemblerDecorator
{
    /** @var DeputyAssembler  */
    private $parentAssembler;

    /** @var ClientAssembler */
    private $clientAssembler;

    public function __construct(DeputyAssembler $parentAssembler, ClientAssembler $clientAssembler)
    {
        $this->parentAssembler = $parentAssembler;
        $this->clientAssembler = $clientAssembler;
    }

    /**
     * @param array $data
     * @return DeputyDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = $this->parentAssembler->assembleFromArray($data);

        if (isset($data['clients']) && is_array($data['clients'])) {
            $dto->setClients($this->assembleDeputyClients($data['clients']));
        }

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
            $dtos[] = $this->clientAssembler->assembleFromArray($client);
        }

        return $dtos;
    }
}
