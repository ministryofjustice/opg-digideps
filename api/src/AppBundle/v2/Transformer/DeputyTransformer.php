<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\OrganisationDto;

class DeputyTransformer
{
    /** @var ClientTransformer */
    private $clientTransformer;

    /** @var OrganisationTransformer */
    private $organisationTransformer;

    /**
     * @param ClientTransformer $clientTransformer
     * @param OrganisationTransformer $organisationTransformer
     */
    public function __construct(ClientTransformer $clientTransformer, OrganisationTransformer $organisationTransformer)
    {
        $this->clientTransformer = $clientTransformer;
        $this->organisationTransformer = $organisationTransformer;
    }

    /**
     * @param DeputyDto $dto
     * @return array
     */
    public function transform(DeputyDto $dto)
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
            'phone_main' => $dto->getPhoneMain()
        ];

        $data['clients'] = (null === $dto->getClients()) ? [] : $this->transformClients($dto->getClients());
        $data['organisations'] = (null === $dto->getOrganisation()) ? [] : $this->transformOrganisation($dto->getOrganisation());

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

    /**
     * @param OrganisationDto $organisationDto
     * @return array
     */
    private function transformOrganisation(OrganisationDto $organisationDto): array
    {
        return [$this->organisationTransformer->transform($organisationDto)];
    }
}
