<?php

namespace App\v2\Transformer;

use App\v2\DTO\ClientDto;
use App\v2\DTO\UserDto;

class UserTransformer
{
    public function __construct(private readonly ClientTransformer $clientTransformer)
    {
    }

    /**
     * @return array
     */
    public function transform(UserDto $dto, array $exclude = [])
    {
        $data = [
            'id' => $dto->getId(),
            'firstname' => $dto->getFirstName(),
            'lastname' => $dto->getLastName(),
            'email' => $dto->getEmail(),
            'role_name' => $dto->getRoleName(),
            'address_postcode' => $dto->getAddressPostcode(),
            'ndr_enabled' => $dto->getNdrEnabled(),
            'active' => $dto->isActive(),
            'job_title' => $dto->getJobTitle(),
            'phone_main' => $dto->getPhoneMain(),
            'last_logged_in' => $dto->getLastLoggedIn() instanceof \DateTime ? $dto->getLastLoggedIn()->format('Y-m-d H:i:s') : null,
        ];

        if (!in_array('clients', $exclude) && $dto->getClients()) {
            $data['clients'] = $this->transformClients($dto->getClients());
        }

        return $data;
    }

    /**
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
