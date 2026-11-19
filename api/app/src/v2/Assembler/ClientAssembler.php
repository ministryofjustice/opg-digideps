<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Assembler;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\v2\Assembler\Report\ReportSummaryAssembler;
use OPG\Digideps\Backend\v2\DTO\ClientDto;
use OPG\Digideps\Backend\v2\DTO\DtoPropertySetterTrait;
use OPG\Digideps\Backend\v2\DTO\OrganisationDto;
use OPG\Digideps\Backend\v2\DTO\UserDto;
use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDto;

class ClientAssembler
{
    use DtoPropertySetterTrait;

    public function __construct(
        private readonly ReportSummaryAssembler $reportDtoAssembler,
        private readonly DeputyAssembler $deputyAssembler,
    ) {
    }

    public function assembleFromArray(
        array $data,
        Organisation|OrganisationDto|array|null $orgDto = null,
    ): ClientDto {
        $dto = new ClientDto();

        // exclude reports and deputies as these will be assembled separately
        $this->setPropertiesFromData($dto, $data, ['reports', 'deputy']);

        if (isset($data['reports']) && is_array($data['reports'])) {
            $dtos = [];
            /** @var array $report */
            foreach ($data['reports'] as $report) {
                $dtos[] = $this->reportDtoAssembler->assembleFromArray($report);
            }
            $dto->setReports($dtos);
            $dto->setReportCount(count($dtos));
        }

        if (isset($data['organisation']) && is_array($data['organisation'])) {
            $dto->setOrganisation($orgDto);
        }

        if (isset($data['deputy']) && is_array($data['deputy'])) {
            $dto->setDeputy($this->deputyAssembler->assembleFromArray($data['deputy']));
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
        $dto->setPossibleReportReplacements($client->findPossibleReportReplacements());

        $email = $client->getEmail();
        if (!empty($email)) {
            $dto->setEmail($email);
        }

        $archivedAt = $client->getArchivedAt();
        if ($archivedAt !== null) {
            $dto->setArchivedAt($archivedAt);
        }

        $deletedAt = $client->getDeletedAt();
        if ($deletedAt !== null) {
            $dto->setDeletedAt($deletedAt);
        }

        $reports = [];
        foreach ($client->getReports() as $report) {
            $reports[] = $this->reportDtoAssembler->assembleFromEntity($report);
        }
        $dto->setReports($reports);
        $dto->setReportCount(count($reports));

        $deputy = $client->getDeputy();
        if ($deputy !== null) {
            $dto->setDeputy($this->deputyAssembler->assembleFromEntity($deputy));
        }

        $users = [];
        foreach ($client->getUsers() as $user) {
            $users[] = $this->assembleUserDtoFromEntity($user);
        }
        $dto->setDeputies($users);

        return $dto;
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
            ->setCourtDate($dto->getOrderDate());

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

    // built here rather than via UserAssembler, which depends on this class
    private function assembleUserDtoFromEntity(User $user): UserDto
    {
        $dto = new UserDto();

        $this->setPropertiesFromData($dto, [
            'id' => $user->getId(),
            'firstName' => $user->getFirstname(),
            'lastName' => $user->getLastname(),
            'email' => $user->getEmail(),
            'roleName' => $user->getRoleName(),
            'address1' => $user->getAddress1(),
            'address2' => $user->getAddress2(),
            'address3' => $user->getAddress3(),
            'addressPostcode' => $user->getAddressPostcode(),
            'addressCountry' => $user->getAddressCountry(),
            'active' => $user->getActive(),
            'jobTitle' => $user->getJobTitle(),
            'phoneMain' => $user->getPhoneMain(),
            'lastLoggedIn' => $user->getLastLoggedIn(),
            'deputyUid' => $user->getDeputyUid(),
        ]);

        return $dto;
    }
}
