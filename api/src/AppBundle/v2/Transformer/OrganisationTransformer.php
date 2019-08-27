<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\OrganisationDto;

class OrganisationTransformer
{
    /** @var DeputyTransformer */
    private $deputyTransformer;

    /** @var ClientTransformer */
    private $clientTransformer;

    /**
     * @param DeputyTransformer $deputyTransformer
     * @param ClientTransformer $clientTransformer
     */
    public function __construct(DeputyTransformer $deputyTransformer = null, ClientTransformer $clientTransformer = null)
    {
        $this->deputyTransformer = $deputyTransformer;
        $this->clientTransformer = $clientTransformer;
    }

    /**
     * @param OrganisationDto $dto
     * @param array $exclude
     * @return array
     */
    public function transform(OrganisationDto $dto, array $exclude = []): array
    {
        $data = [
            'id' => $dto->getId(),
            'name' => $dto->getName(),
            'email_identifier' => $dto->getEmailIdentifier(),
            'is_activated' => $dto->isActivated()
        ];

        if (!in_array('users', $exclude)) {
            $data['users'] = $this->transformUsers($dto->getUsers());
        }

        if (!in_array('clients', $exclude)) {
            $data['clients'] = $this->transformClients($dto->getClients());
        }

        return $data;
    }

    /**
     * @param array $users
     * @return array
     */
    private function transformUsers(array $users): array
    {
        if (empty($users)) {
            return [];
        }

        $transformed = [];

        foreach ($users as $user) {
            if ($user instanceof DeputyDto) {
                $transformed[] = $this->deputyTransformer->transform($user, ['clients']);
            }
        }

        return $transformed;
    }

    /**
     * @param array $clients
     * @return array
     */
    private function transformClients(array $clients): array
    {
        if (empty($clients)) {
            return [];
        }

        $transformed = [];

        foreach ($clients as $client) {
            if ($client instanceof ClientDto) {
                $transformed[] = $this->clientTransformer->transform($client, ['reports', 'ndr', 'organisations']);
            }
        }

        return $transformed;
    }
}
