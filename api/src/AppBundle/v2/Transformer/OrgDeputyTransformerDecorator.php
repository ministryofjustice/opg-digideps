<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\OrganisationDto;

class OrgDeputyTransformerDecorator
{
    /** @var DeputyTransformer */
    private $baseTransformer;

    /** @var OrganisationTransformer */
    private $organisationTransformer;

    /**
     * @param DeputyTransformer $baseTransformer
     * @param OrganisationTransformer $organisationTransformer
     */
    public function __construct(DeputyTransformer $baseTransformer, OrganisationTransformer $organisationTransformer)
    {
        $this->baseTransformer = $baseTransformer;
        $this->organisationTransformer = $organisationTransformer;
    }

    /**
     * @param DeputyDto $dto
     * @return array
     */
    public function transform(DeputyDto $dto)
    {
        $data = $this->baseTransformer->transform($dto);

        $data['organisation'] = (null === $dto->getOrganisation()) ? null : $this->transformOrganisation($dto->getOrganisation());

        return $data;
    }

    /**
     * @param OrganisationDto $organisationDto
     * @return array
     */
    private function transformOrganisation(OrganisationDto $organisationDto): array
    {
        return $this->organisationTransformer->transform($organisationDto);
    }
}
