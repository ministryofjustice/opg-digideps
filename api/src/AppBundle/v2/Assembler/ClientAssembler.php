<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\Assembler\Report\ReportAssemblerInterface;
use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\DtoPropertySetterTrait;

class ClientAssembler
{
    use DtoPropertySetterTrait;

    /** @var ReportAssemblerInterface  */
    private $reportDtoAssembler;

    /** @var NdrAssembler */
    private $ndrDtoAssembler;

    /** @var OrganisationAssembler */
    private $organisationDtoAssembler;

    /**
     * @param ReportAssemblerInterface $reportDtoAssembler
     * @param NdrAssembler $ndrDtoAssembler
     * @param OrganisationAssembler $organisationDtoAssembler
     */
    public function __construct(ReportAssemblerInterface $reportDtoAssembler, NdrAssembler $ndrDtoAssembler, OrganisationAssembler $organisationDtoAssembler)
    {
        $this->reportDtoAssembler = $reportDtoAssembler;
        $this->ndrDtoAssembler = $ndrDtoAssembler;
        $this->organisationDtoAssembler = $organisationDtoAssembler;
    }

    /**
     * @param array $data
     * @return ClientDto
     */
    public function assembleFromArray(array $data)
    {
        $dto = new ClientDto();

        $exclude = ['ndr', 'reports'];
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
}
