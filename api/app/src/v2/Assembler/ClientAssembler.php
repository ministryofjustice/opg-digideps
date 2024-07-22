<?php

namespace App\v2\Assembler;

use App\Entity\Client;
use App\v2\Assembler\Report\ReportAssemblerInterface;
use App\v2\DTO\ClientDto;
use App\v2\DTO\DtoPropertySetterTrait;
use App\v2\DTO\OrganisationDto;
use App\v2\DTO\UserDto;
use App\v2\Registration\DTO\OrgDeputyshipDto;

class ClientAssembler
{
    use DtoPropertySetterTrait;

    /** @var ReportAssemblerInterface */
    private $reportDtoAssembler;

    /** @var NdrAssembler */
    private $ndrDtoAssembler;

    /** @var DeputyAssembler */
    private $deputyAssembler;

    /**
     * ClientAssembler constructor.
     */
    public function __construct(
        ReportAssemblerInterface $reportDtoAssembler,
        NdrAssembler $ndrDtoAssembler,
        DeputyAssembler $deputyDtoAssembler
    ) {
        $this->reportDtoAssembler = $reportDtoAssembler;
        $this->ndrDtoAssembler = $ndrDtoAssembler;
        $this->deputyAssembler = $deputyDtoAssembler;
    }

    /**
     * @return ClientDto
     */
    public function assembleFromArray(array $data, ?OrganisationDto $orgDto = null)
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

        if (isset($data['deputies']) && is_array($data['deputies'])) {
            $dto->setDeputies($this->assembleClientDeputies($data['deputies']));
        }

        if (isset($data['users']) && is_array($data['users'])) {
            $dto->setUsers($this->assembleClientUsers($data['users']));
        }

        return $dto;
    }

    /**
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

    private function assembleClientNdr(array $ndr)
    {
        return $this->ndrDtoAssembler->assembleFromArray($ndr);
    }

    private function assembleClientDeputies(array $deputies)
    {
        $dtos = [];

        foreach ($deputies as $deputy) {
            $dtos[] = $this->deputyAssembler->assembleFromArray($deputy);
        }

        return $dtos;
    }

    public function assembleFromOrgDeputyshipDto(OrgDeputyshipDto $dto)
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

    private function assembleClientUsers(array $users)
    {
        $dtos = [];

        foreach ($users as $user) {
            $dto = new UserDto();
            $this->setPropertiesFromData($dto, $user, ['clients']);
            $dtos[] = $dto;
        }

        return $dtos;
    }
}
