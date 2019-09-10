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

    /** @var NamedDeputyAssembler  */
    private $namedDeputyAssembler;

    /**
     * ClientAssembler constructor.
     * @param ReportAssemblerInterface $reportDtoAssembler
     * @param NdrAssembler $ndrDtoAssembler
     * @param OrganisationAssembler $organisationDtoAssembler
     * @param NamedDeputyAssembler $namedDeputyDtoAssembler
     */
    public function __construct(
        ReportAssemblerInterface $reportDtoAssembler,
        NdrAssembler $ndrDtoAssembler,
        OrganisationAssembler $organisationDtoAssembler,
        NamedDeputyAssembler $namedDeputyDtoAssembler
    ) {

        $this->reportDtoAssembler = $reportDtoAssembler;
        $this->ndrDtoAssembler = $ndrDtoAssembler;
        $this->organisationDtoAssembler = $organisationDtoAssembler;
        $this->namedDeputyAssembler = $namedDeputyDtoAssembler;
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

        if (isset($data['organisations']) && is_array($data['organisations'])) {
            $dto->setOrganisations($this->assembleClientOrganisations($data['organisations']));
        }

        if (isset($data['namedDeputy']) && is_array($data['namedDeputy'])) {
            $dto->setNamedDeputy($this->assembleClientNamedDeputy($data['namedDeputy']));
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

    private function assembleClientOrganisations(array $organisations)
    {
        $dtos = [];

        foreach ($organisations as $organisation) {
            $dtos[] = $this->organisationDtoAssembler->assembleFromArray($organisation);
        }

        return $dtos;
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
     * @param array $namedDeputy
     * @return
     */
    private function assembleClientNamedDeputy(array $namedDeputy)
    {
        return $this->namedDeputyAssembler->assembleFromArray($namedDeputy);
    }

}
