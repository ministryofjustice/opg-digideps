<?php

namespace OPG\Digideps\Backend\v2\Transformer;

use OPG\Digideps\Backend\v2\DTO\ClientDto;
use OPG\Digideps\Backend\v2\DTO\DeputyDto;
use OPG\Digideps\Backend\v2\DTO\ReportDto;
use OPG\Digideps\Backend\v2\DTO\UserDto;

class ClientTransformer
{
    public function __construct(
        private readonly ReportTransformer $reportTransformer,
        private readonly DeputyTransformer $deputyTransformer
    ) {
    }

    public function transform(ClientDto $dto, array $exclude = [], ?array $org = null): array
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

        if (!in_array('organisation', $exclude) && $org !== null) {
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
    private function transformArchivedAt(ClientDto $dto): ?string
    {
        return $dto->getArchivedAt() instanceof \DateTime ? $dto->getArchivedAt()->format('Y-m-d H:i:s') : null;
    }

    private function transformDeletedAt(ClientDto $dto): ?string
    {
        return $dto->getDeletedAt() instanceof \DateTime ? $dto->getDeletedAt()->format('Y-m-d H:i:s') : null;
    }

    private function transformReports(array $reports): array
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

    private function transformDeputy(DeputyDto $deputy): array
    {
        return $this->deputyTransformer->transform($deputy);
    }

    /**
     * @return array{
     *     id: (int | null),
     *     firstname: (string | null),
     *     lastname: (string | null),
     *     email: (string | null),
     *     role_name: (string | null),
     *     address1: (string | null),
     *     address2: (string | null),
     *     address3: (string | null),
     *     address_postcode: (string | null),
     *     address_country: (string | null),
     *     active: (bool | null),
     *     job_title: (string | null),
     *     phone_main: (string | null),
     *     last_logged_in: (mixed | null),
     *     deputy_uid: (int | null),
     *     is_primary: (bool | null)}[]
     */
    private function transformDeputies(array $userDtos): array
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
