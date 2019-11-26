<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\DTO\OrganisationDto;

class OrgDeputyAssemblerDecorator
{
    /** @var DeputyAssembler  */
    private $parentAssembler;

    /** @var OrganisationAssembler */
    private $organisationAssembler;

    public function __construct(DeputyAssembler $parentAssembler, OrganisationAssembler $organisationAssembler)
    {
        $this->parentAssembler = $parentAssembler;
        $this->organisationAssembler = $organisationAssembler;
    }

    /**
     * @param array $data
     * @return DeputyDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = $this->parentAssembler->assembleFromArray($data);

        if (isset($data['organisations']) && is_array($data['organisations']) && isset($data['organisations'][0])) {
            $dto->setOrganisation($this->assembleDeputyOrganisation($data['organisations'][0]));
        }

        return $dto;
    }

    /**
     * @param array $organisation
     * @return OrganisationDto
     */
    private function assembleDeputyOrganisation(array $organisation)
    {
        return $this->organisationAssembler->assembleFromArray($organisation);
    }
}
