<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DeputyDto;

class LayDeputyTransformerDecorator
{
    /** @var DeputyTransformer */
    private $baseTransformer;

    /** @var ClientTransformer */
    private $clientTransformer;

    /**
     * @param DeputyTransformer $baseTransformer
     * @param ClientTransformer $clientTransformer
     */
    public function __construct(DeputyTransformer $baseTransformer, ClientTransformer $clientTransformer)
    {
        $this->baseTransformer = $baseTransformer;
        $this->clientTransformer = $clientTransformer;
    }

    /**
     * @param DeputyDto $dto
     * @return array
     */
    public function transform(DeputyDto $dto)
    {
        $data = $this->baseTransformer->transform($dto);

        $data['clients'] = (null === $dto->getClients()) ? [] : $this->transformClients($dto->getClients());

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
