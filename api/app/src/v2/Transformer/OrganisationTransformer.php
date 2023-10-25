<?php

namespace App\v2\Transformer;

use App\v2\DTO\ClientDto;
use App\v2\DTO\DeputyDto;
use App\v2\DTO\OrganisationDto;

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

    public function transform(OrganisationDto $dto, array $exclude = []): array
    {
        $data = [
            'id' => $dto->getId(),
            'name' => $dto->getName(),
            'email_identifier' => $dto->getEmailIdentifier(),
            'is_activated' => $dto->isActivated(),
        ];

        if (!in_array('total_user_count', $exclude) && $dto->getTotalUserCount()) {
            $data['total_user_count'] = $dto->getTotalUserCount();
        }

        if (!in_array('total_client_count', $exclude) && $dto->getTotalClientCount()) {
            $data['total_client_count'] = $dto->getTotalClientCount();
        }

        if (!in_array('users', $exclude) && $dto->getUsers()) {
            $data['users'] = $this->transformUsers($dto->getUsers());
        }

        if (!in_array('clients', $exclude) && $dto->getClients()) {
            $data['clients'] = $this->transformClients($dto->getClients());
        }

        return $data;
    }

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

    private function transformClients(array $clients, array $transformedOrg = null): array
    {
        if (empty($clients)) {
            return [];
        }

        $transformed = [];

        foreach ($clients as $client) {
            if ($client instanceof ClientDto) {
                $transformed[] = $this->clientTransformer->transform($client, ['reports', 'ndr', 'organisation']);
            }
        }

        return $transformed;
    }
}
