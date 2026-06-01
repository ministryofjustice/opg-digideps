<?php

namespace OPG\Digideps\Backend\v2\Assembler;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\v2\Assembler\Report\ReportAssemblerInterface;
use OPG\Digideps\Backend\v2\DTO\ClientDto;
use OPG\Digideps\Backend\v2\DTO\DeputyDto;
use OPG\Digideps\Backend\v2\DTO\DtoPropertySetterTrait;
use OPG\Digideps\Backend\v2\DTO\OrganisationDto;
use OPG\Digideps\Backend\v2\DTO\ReportDto;
use OPG\Digideps\Backend\v2\DTO\UserDto;
use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDto;
use OPG\Digideps\Backend\v2\Registration\DTO\OrgDeputyshipDto;

class ClientAssembler
{
    use DtoPropertySetterTrait;

    public function __construct(
        private readonly ReportAssemblerInterface $reportDtoAssembler,
        private readonly DeputyAssembler $deputyAssembler
    ) {
    }

    public function assembleFromArray(array $data, Organisation|OrganisationDto|array|null $orgDto = null): ClientDto
    {
        $dto = new ClientDto();

        $exclude = ['reports', 'deputy'];
        $this->setPropertiesFromData($dto, $data, $exclude);

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
     * @return array<ReportDto>
     */
    private function assembleClientReports(array $reports): array
    {
        $dtos = [];

        foreach ($reports as $report) {
            $dtos[] = $this->reportDtoAssembler->assembleFromArray($report);
        }

        return $dtos;
    }

    private function assembleClientDeputy(array $deputy): DeputyDto
    {
        return $this->deputyAssembler->assembleFromArray($deputy);
    }

    public function assembleFromOrgDeputyshipDto(OrgDeputyshipDto $dto): Client
    {
        $client = new Client()
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

    public function assembleFromLayDeputyshipDto(LayDeputyshipDto $dto): Client
    {
        $client = new Client()
            ->setCaseNumber($dto->getCaseNumber())
            ->setFirstname($dto->getClientFirstname() ?: null)
            ->setLastname($dto->getClientSurname())
            ->setAddress($dto->getClientAddress1() ?: null)
            ->setAddress2($dto->getClientAddress2() ?: null)
            ->setAddress3($dto->getClientAddress3() ?: null)
            ->setAddress4($dto->getClientAddress4() ?: null)
            ->setAddress5($dto->getClientAddress5() ?: null)
            ->setPostcode($dto->getClientPostcode() ?: null)
            ->setCourtDate($dto->getOrderDate() ?: null);

        if (!empty($dto->getClientPostCode())) {
            $client->setPostcode($dto->getClientPostCode());
            $client->setCountry('GB'); // postcode given means a UK address is given
        }

        return $client;
    }

    /**
     * @return array<UserDto>
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
}
