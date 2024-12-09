<?php

namespace App\v2\Transformer;

use App\v2\DTO\ClientDto;
use App\v2\DTO\DeputyDto;
use App\v2\DTO\NdrDto;
use App\v2\DTO\ReportDto;
use App\v2\DTO\UserDto;

class ClientTransformer
{
    /** @var ReportTransformer */
    private $reportTransformer;

    /** @var NdrTransformer */
    private $ndrTransformer;

    /** @var DeputyTransformer */
    private $deputyTransformer;

    public function __construct(
        ReportTransformer $reportTransformer,
        NdrTransformer $ndrTransformer,
        DeputyTransformer $deputyTransformer,
    ) {
        $this->reportTransformer = $reportTransformer;
        $this->ndrTransformer = $ndrTransformer;
        $this->deputyTransformer = $deputyTransformer;
    }

    /**
     * @return array
     */
    public function transform(ClientDto $dto, array $exclude = [], ?array $org = null)
    {
        $transformed = [
            'id' => $dto->getId(),
            'case_number' => $dto->getCaseNumber(),
            'firstname' => $dto->getFirstName(),
            'lastname' => $dto->getLastName(),
            'email' => $dto->getEmail(),
            'archived_at' => $this->transformArchivedAt($dto),
            'deleted_at' => $this->transformDeletedAt($dto),
            'total_report_count' => $dto->getReportCount(),
        ];

        if (!in_array('reports', $exclude) && !empty($dto->getReports())) {
            $transformed['reports'] = $this->transformReports($dto->getReports());
        }

        if (!in_array('ndr', $exclude) && $dto->getNdr() instanceof NdrDto) {
            $transformed['ndr'] = $this->transformNdr($dto->getNdr());
        }

        if (!in_array('organisation', $exclude) && null !== $org) {
            $transformed['organisation'] = $org;
        }

        if (!in_array('deputy', $exclude) && $dto->getDeputy() instanceof DeputyDto) {
            $transformed['deputy'] = $this->transformDeputy($dto->getDeputy());
        }

        if (!in_array('deputies', $exclude) && !empty($dto->getDeputies())) {
            $transformed['users'] = $this->transformDeputies($dto->getDeputies());
        }

        return $transformed;
    }

    /**
     * @return string|null
     */
    private function transformArchivedAt(ClientDto $dto)
    {
        return $dto->getArchivedAt() instanceof \DateTime ? $dto->getArchivedAt()->format('Y-m-d H:i:s') : null;
    }

    /**
     * @return string|null
     */
    private function transformDeletedAt(ClientDto $dto)
    {
        return $dto->getDeletedAt() instanceof \DateTime ? $dto->getDeletedAt()->format('Y-m-d H:i:s') : null;
    }

    /**
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
     * @return array
     */
    private function transformNdr(NdrDto $ndr)
    {
        return $this->ndrTransformer->transform($ndr);
    }

    /**
     * @return array
     */
    private function transformDeputy(DeputyDto $deputy)
    {
        return $this->deputyTransformer->transform($deputy);
    }

    private function transformDeputies(array $userDtos)
    {
        if (empty($userDtos)) {
            return [];
        }

        $transformed = [];

        foreach ($userDtos as $userDto) {
            if ($userDto instanceof UserDto) {
                $transformed[] = [
                    'id' => $userDto->getId(),
                    'firstname' => $userDto->getFirstName(),
                    'lastname' => $userDto->getLastName(),
                    'email' => $userDto->getEmail(),
                    'role_name' => $userDto->getRoleName(),
                    'address1' => $userDto->getAddress1(),
                    'address2' => $userDto->getAddress2(),
                    'address3' => $userDto->getAddress3(),
                    'address_postcode' => $userDto->getAddressPostcode(),
                    'address_country' => $userDto->getAddressCountry(),
                    'ndr_enabled' => $userDto->getNdrEnabled(),
                    'active' => $userDto->isActive(),
                    'job_title' => $userDto->getJobTitle(),
                    'phone_main' => $userDto->getPhoneMain(),
                    'last_logged_in' => $userDto->getLastLoggedIn() instanceof \DateTime ? $userDto->getLastLoggedIn()->format('Y-m-d H:i:s') : null,
                    'deputy_uid' => $userDto->getDeputyUid(),
                    'is_primary' => $userDto->getIsPrimary(),
                ];
            }
        }

        return $transformed;
    }
}
