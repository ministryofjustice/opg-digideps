<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\OrganisationDto;

class DeputyTransformer
{
    /**
     * @param DeputyDto $dto
     * @return array
     */
    public function transform(DeputyDto $dto)
    {
        return [
            'id' => $dto->getId(),
            'firstname' => $dto->getFirstName(),
            'lastname' => $dto->getLastName(),
            'email' => $dto->getEmail(),
            'role_name' => $dto->getRoleName(),
            'address_postcode' => $dto->getAddressPostcode(),
            'ndr_enabled' => $dto->getNdrEnabled(),
            'active' => $dto->isActive(),
            'job_title' => $dto->getJobTitle(),
            'phone_main' => $dto->getPhoneMain()
        ];
    }
}
