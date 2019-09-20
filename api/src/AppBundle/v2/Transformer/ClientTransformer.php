<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\NdrDto;
use AppBundle\v2\DTO\OrganisationDto;
use AppBundle\v2\DTO\ReportDto;
use Symfony\Component\Intl\Exception\NotImplementedException;

class ClientTransformer
{
    /** @var ReportTransformer */
    private $reportTransformer;

    /** @var NdrTransformer */
    private $ndrTransformer;

    /** @var OrganisationTransformer */
    private $organisationTransformer;

    /**
     * @param ReportTransformer $reportTransformer
     * @param NdrTransformer $ndrTransformer
     * @param OrganisationTransformer $organisationTransformer
     */
    public function __construct(ReportTransformer $reportTransformer, NdrTransformer $ndrTransformer, OrganisationTransformer $organisationTransformer)
    {
        $this->reportTransformer = $reportTransformer;
        $this->ndrTransformer = $ndrTransformer;
        $this->organisationTransformer = $organisationTransformer;
    }

    /**
     * @param ClientDto $dto
     * @param array $exclude
     * @return array
     */
    public function transform(ClientDto $dto, array $exclude = [])
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

        if (!in_array('reports', $exclude)) {
            $transformed['reports'] = $this->transformReports($dto->getReports());
        }

        if (!in_array('ndr', $exclude) && $dto->getNdr() instanceof NdrDto) {
            $transformed['ndr'] = $this->transformNdr($dto->getNdr());
        }

        if (!in_array('organisation', $exclude) && $dto->getOrganisation() !== null) {
            $transformed['organisation'] = $this->transformOrganisation($dto->getOrganisation());
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
}
