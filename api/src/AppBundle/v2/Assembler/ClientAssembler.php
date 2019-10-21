<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\Assembler\Report\ReportAssemblerInterface;
use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DtoPropertySetterTrait;
use AppBundle\Entity\Client;

class ClientAssembler
{
    use DtoPropertySetterTrait;

    /** @var ReportAssemblerInterface  */
    private $reportDtoAssembler;

    /** @var NdrAssembler */
    private $ndrDtoAssembler;

    /** @var OrganisationAssembler */
    private $organisationDtoAssembler;

    /** @var DeputyAssembler */
    private $deputyDtoAssembler;

    /**
     * ClientAssembler constructor.
     * @param ReportAssemblerInterface $reportDtoAssembler
     * @param NdrAssembler $ndrDtoAssembler
     * @param OrganisationAssembler $organisationDtoAssembler
     * @param DeputyAssembler $deputyDtoAssembler
     */
    public function __construct(
        ReportAssemblerInterface $reportDtoAssembler,
        NdrAssembler $ndrDtoAssembler,
        OrganisationAssembler $organisationDtoAssembler,
        DeputyAssembler $deputyDtoAssembler
    )
    {
        $this->reportDtoAssembler = $reportDtoAssembler;
        $this->ndrDtoAssembler = $ndrDtoAssembler;
        $this->organisationDtoAssembler = $organisationDtoAssembler;
        $this->deputyDtoAssembler = $deputyDtoAssembler;
    }

    /**
     * @param array $data
     * @return ClientDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new ClientDto();

        $exclude = ['ndr', 'reports', 'namedDeputy'];
        $this->setPropertiesFromData($dto, $data, $exclude);

        if (isset($data['ndr']) && is_array($data['ndr'])) {
            $dto->setNdr($this->assembleClientNdr($data['ndr']));
        }

        if (isset($data['reports'])  && is_array($data['reports'])) {
            $dto->setReports($this->assembleClientReports($data['reports']));
            $dto->setReportCount(count($data['reports']));
        }

        if (isset($data['organisation']) && is_array($data['organisation'])) {
            $dto->setOrganisation($this->assembleClientOrganisation($data['organisation']));
        }

        if (isset($data['namedDeputy']) && is_array($data['namedDeputy'])) {
            $dto->setNamedDeputy($this->assembleClientDeputy($data['namedDeputy']));
        }

        return $dto;
    }

    /**
     * @param Client $client
     * @return ClientDto
     */
    public function assembleFromEntity(Client $client)
    {
        $dto = new ClientDto();

        $dto->setId($client->getId());
        $dto->setCaseNumber($client->getCaseNumber());
        $dto->setFirstName($client->getFirstname());
        $dto->setLastName($client->getLastname());
        $dto->setReportCount($client->getTotalReportCount());

        return $dto;
    }

    /**
     * @param array $reports
     * @return array
     */
    private function assembleClientReports(array $reports)
    {
        $dtos = [];

        foreach ($reports as $report) {

            $dtos[] = $this->reportDtoAssembler->assembleFromArray($report);
        }

        return $dtos;
    }

    private function assembleClientOrganisation(array $organisation)
    {
        return $this->organisationDtoAssembler->assembleFromArray($organisation);
    }

    /**
     * @param array $ndr
     * @return
     */
    private function assembleClientNdr(array $ndr)
    {
        return $this->ndrDtoAssembler->assembleFromArray($ndr);
    }

    /**
     * @param array $deputy
     * @return
     */
    private function assembleClientDeputy(array $deputy)
    {
        return $this->deputyDtoAssembler->assembleFromArray($deputy);
    }
}
