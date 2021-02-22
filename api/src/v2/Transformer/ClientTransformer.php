<?php

namespace App\v2\Transformer;

use App\v2\DTO\ClientDto;
use App\v2\DTO\DeputyDto;
use App\v2\DTO\NamedDeputyDto;
use App\v2\DTO\NdrDto;
use App\v2\DTO\OrganisationDto;
use App\v2\DTO\ReportDto;

class ClientTransformer
{
    /** @var ReportTransformer */
    private $reportTransformer;

    /** @var NdrTransformer */
    private $ndrTransformer;

    /** @var NamedDeputyTransformer */
    private $namedDeputyTransformer;

    /**
     * @param ReportTransformer $reportTransformer
     * @param NdrTransformer $ndrTransformer
     * @param NamedDeputyTransformer $namedDeputyTransformer
     */
    public function __construct(
        ReportTransformer $reportTransformer,
        NdrTransformer $ndrTransformer,
        NamedDeputyTransformer $namedDeputyTransformer
    ) {
        $this->reportTransformer = $reportTransformer;
        $this->ndrTransformer = $ndrTransformer;
        $this->namedDeputyTransformer = $namedDeputyTransformer;
    }

    /**
     * @param ClientDto $dto
     * @param array $exclude
     * @return array
     */
    public function transform(ClientDto $dto, array $exclude = [], ?OrganisationDto $orgDto = null)
    {
        $transformed = [
            'id' => $dto->getId(),
            'case_number' => $dto->getCaseNumber(),
            'firstname' => $dto->getFirstName(),
            'lastname' => $dto->getLastName(),
            'email' => $dto->getEmail(),
            'archived_at' => $this->transformArchivedAt($dto),
            'deleted_at' => $this->transformDeletedAt($dto),
            'total_report_count' => $dto->getReportCount()
        ];

        if (!in_array('reports', $exclude) && !empty($dto->getReports())) {
            $transformed['reports'] = $this->transformReports($dto->getReports());
        }

        if (!in_array('ndr', $exclude) && $dto->getNdr() instanceof NdrDto) {
            $transformed['ndr'] = $this->transformNdr($dto->getNdr());
        }

        if (!in_array('organisation', $exclude) && $orgDto !== null) {
            $transformed['organisation'] = $orgDto;
        }

        if (!in_array('namedDeputy', $exclude) && $dto->getNamedDeputy() instanceof NamedDeputyDto) {
            $transformed['named_deputy'] = $this->transformNamedDeputy($dto->getNamedDeputy());
        }

        if (!in_array('deputies', $exclude) && !empty($dto->getDeputies())) {
            $transformed['users'] = $this->transformDeputies($dto->getDeputies());
        }

        return $transformed;
    }

    /**
     * @param ClientDto $dto
     * @return null|string
     */
    private function transformArchivedAt(ClientDto $dto)
    {
        return $dto->getArchivedAt() instanceof \DateTime ? $dto->getArchivedAt()->format('Y-m-d H:i:s') : null;
    }

    /**
     * @param ClientDto $dto
     * @return null|string
     */
    private function transformDeletedAt(ClientDto $dto)
    {
        return $dto->getDeletedAt() instanceof \DateTime ? $dto->getDeletedAt()->format('Y-m-d H:i:s') : null;
    }

    /**
     * @param array $reports
     * @return array
     */
    private function transformReports(array $reports)
    {
        if (empty($reports)) {
            return [];
        }

        $transformed = [];

        foreach ($reports as $report) {
            if ($report instanceof ReportDto) {
                $transformed[] = $this->reportTransformer->transform($report);
            }
        }

        return $transformed;
    }

    /**
     * @param OrganisationDto $organisation
     * @return array
     */
    private function transformOrganisation(OrganisationDto $organisation)
    {
        return $this->organisationTransformer->transform($organisation, ['users']);
    }

    /**
     * @param NdrDto $ndr
     * @return array
     */
    private function transformNdr(NdrDto $ndr)
    {
        return $this->ndrTransformer->transform($ndr);
    }

    /**
     * @param NamedDeputyDto $namedDeputy
     * @return array
     */
    private function transformNamedDeputy(NamedDeputyDto $namedDeputy)
    {
        return $this->namedDeputyTransformer->transform($namedDeputy);
    }

    private function transformDeputies(array $deputyDtos)
    {
        if (empty($deputyDtos)) {
            return [];
        }

        $transformed = [];

        foreach ($deputyDtos as $deputyDto) {
            if ($deputyDto instanceof DeputyDto) {
                $transformed[] = [
                    'id' => $deputyDto->getId(),
                    'firstname' => $deputyDto->getFirstName(),
                    'lastname' => $deputyDto->getLastName(),
                    'email' => $deputyDto->getEmail(),
                    'role_name' => $deputyDto->getRoleName(),
                    'address1' => $deputyDto->getAddress1(),
                    'address2' => $deputyDto->getAddress2(),
                    'address3' => $deputyDto->getAddress3(),
                    'address_postcode' => $deputyDto->getAddressPostcode(),
                    'address_country' => $deputyDto->getAddressCountry(),
                    'ndr_enabled' => $deputyDto->getNdrEnabled(),
                    'active' => $deputyDto->isActive(),
                    'job_title' => $deputyDto->getJobTitle(),
                    'phone_main' => $deputyDto->getPhoneMain(),
                    'last_logged_in' => $deputyDto->getLastLoggedIn() instanceof \DateTime ? $deputyDto->getLastLoggedIn()->format('Y-m-d H:i:s') : null
                ];
                ;
            }
        }

        return $transformed;
    }
}
