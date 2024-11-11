<?php

namespace App\v2\Assembler;

use App\Entity\Client;
use App\v2\Assembler\Report\ReportAssemblerInterface;
use App\v2\DTO\ClientDto;
use App\v2\DTO\DeputyDto;
use App\v2\DTO\DtoPropertySetterTrait;
use App\v2\DTO\NdrDto;
use App\v2\DTO\OrganisationDto;
use App\v2\DTO\UserDto;
use App\v2\Registration\DTO\LayPreRegistrationDto;
use App\v2\Registration\DTO\OrgDeputyshipDto;

class ClientAssembler
{
    use DtoPropertySetterTrait;

    /**
     * ClientAssembler constructor.
     */
    public function __construct(
        private readonly ReportAssemblerInterface $reportDtoAssembler,
        private readonly NdrAssembler $ndrDtoAssembler,
        private readonly DeputyAssembler $deputyAssembler
    ) {
    }

    public function assembleFromArray(array $data, ?OrganisationDto $orgDto = null): ClientDto
    {
        $dto = new ClientDto();

        $exclude = ['ndr', 'reports', 'deputy'];
        $this->setPropertiesFromData($dto, $data, $exclude);

        if (isset($data['ndr']) && is_array($data['ndr'])) {
            $dto->setNdr($this->assembleClientNdr($data['ndr']));
        }

        if (isset($data['reports']) && is_array($data['reports'])) {
            $dto->setReports($this->assembleClientReports($data['reports']));
            $dto->setReportCount(count($data['reports']));
        }

        if (isset($data['organisation']) && is_array($data['organisation'])) {
            $dto->setOrganisation($orgDto);
        }

        if (isset($data['deputy']) && is_array($data['deputy'])) {
            $dto->setDeputy($this->assembleClientDeputy($data['deputy']));
        }

        if (isset($data['users']) && is_array($data['users'])) {
            $dto->setDeputies($this->assembleClientDeputies($data['users']));
        }

        return $dto;
    }

    public function assembleFromEntity(Client $client): ClientDto
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
     * @return array<int, ClientDto>
     */
    private function assembleClientReports(array $reports): array
    {
        $dtos = [];

        foreach ($reports as $report) {
            $dtos[] = $this->reportDtoAssembler->assembleFromArray($report);
        }

        return $dtos;
    }

    private function assembleClientNdr(array $ndr): NdrDto
    {
        return $this->ndrDtoAssembler->assembleFromArray($ndr);
    }

    private function assembleClientDeputy(array $deputy): DeputyDto
    {
        return $this->deputyAssembler->assembleFromArray($deputy);
    }

    public function assembleFromOrgDeputyshipDto(OrgDeputyshipDto $dto): Client
    {
        return $this->createClientFromDto($dto);
    }

    public function assembleFromLayPreRegistrationDto(LayPreRegistrationDto $dto): Client
    {
        return $this->createClientFromDto($dto);
    }
    
    private function createClientFromDto($dto): Client
    {
        $client = (new Client())
            ->setCaseNumber($dto->getCaseNumber())
            ->setFirstname($dto->getClientFirstname())
            ->setLastname($dto->getClientLastname())
            ->setAddress($dto->getClientAddress1() ?: null)
            ->setAddress2($dto->getClientAddress2() ?: null)
            ->setAddress3($dto->getClientAddress3() ?: null)
            ->setAddress4($dto->getClientAddress4() ?: null)
            ->setAddress5($dto->getClientAddress5() ?: null)
            ->setDateOfBirth($dto->getClientDateOfBirth() ?: null)
            ->setCourtDate($dto->getCourtDate() ?: null);

        if (!empty($dto->getClientPostCode())) {
            $client->setPostcode($dto->getClientPostCode());
            $client->setCountry('GB'); // postcode given means a UK address is given
        }

        return $client;
    }

    /**
     * @return array<int, UserDto>
     */
    private function assembleClientDeputies(array $deputies): array
    {
        $dtos = [];

        foreach ($deputies as $deputy) {
            $dto = new UserDto();
            $this->setPropertiesFromData($dto, $deputy, ['clients']);
            $dtos[] = $dto;
        }

        return $dtos;
    }
    
    public function assembleFromPreRegistrationData(array $preRegRow): Client
    {
        if (!empty($preRegRow['client_case_number']) &&
            !empty($preRegRow['client_firstname']) &&
            !empty($preRegRow['client_lastname']) &&
            !empty($preRegRow['client_address1']) &&
            !empty($preRegRow['client_address2']) &&
            !empty($preRegRow['client_address3'])
        ) {
            $client = new Client();
            $client
                ->setCaseNumber($preRegRow['client_case_number'])
                ->setFirstname($preRegRow['client_firstname'])
                ->setLastname($preRegRow['client_lastname'])
                ->setAddress($preRegRow['client_address1'])
                ->setAddress2($preRegRow['client_address2'])
                ->setAddress3($preRegRow['client_address3'])
                ->setAddress4($preRegRow['client_address4'] ?: null)
                ->setAddress5($preRegRow['client_address5'] ?: null)
                ->setCourtDate((new \DateTime($preRegRow['court_order_date'])));
    
            if (!empty($preRegRow['client_postcode'])) {
                $client->setPostcode($preRegRow['client_postcode']);
                $client->setCountry('GB');
            }
    
            return $client;
        }
    }
}
