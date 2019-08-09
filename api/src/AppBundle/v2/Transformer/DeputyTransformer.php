<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DeputyDto;

class DeputyTransformer
{
    /** @var ClientTransformer */
    private $clientTransformer;

    /**
     * @param ClientTransformer $clientTransformer
     */
    public function __construct(ClientTransformer $clientTransformer)
    {
        $this->clientTransformer = $clientTransformer;
    }

    /**
     * @param DeputyDto $dto
     * @param array $exclude
     * @return array
     */
    public function transform(DeputyDto $dto, array $exclude = [])
    {
        $data = [
            'id' => $dto->getId(),
            'firstname' => $dto->getFirstName(),
            'lastname' => $dto->getLastName(),
            'email' => $dto->getEmail(),
            'role_name' => $dto->getRoleName(),
            'address_postcode' => $dto->getAddressPostcode(),
            'ndr_enabled' => $dto->getNdrEnabled(),
            'active' => $dto->isActive()
        ];

        if (!in_array('clients', $exclude)) {
            $data['clients'] = $this->transformClients($dto->getClients());
        }

        return $data;
    }

    /**
     * @param array $clients
     * @return array
     */
    private function transformClients(array $clients)
    {
        if (empty($clients)) {
            return [];
        }

        $transformed = [];

        foreach ($clients as $client) {
            if ($client instanceof ClientDto) {
                $transformed[] = $this->clientTransformer->transform($client, ['reports', 'ndr']);
            }
        }

        return $transformed;
    }
}
