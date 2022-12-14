<?php

declare(strict_types=1);

namespace App\FixtureFactory;

use App\Entity\PreRegistration;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationFactory as PreRegistrationDTOFactory;
use DateTime;

class PreRegistrationFactory
{
    public function __construct(private PreRegistrationDTOFactory $preRegistrationFactory)
    {
    }

    /**
     * @return Client
     */
    public function create(array $data): PreRegistration
    {
        $caseNumber = str_pad((string) rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $deputyNumber = str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $reportType = $data['reportType'] == 'ndr' ? 'OPG102' : $data['reportType'];
            
        $dto = (new LayDeputyshipDto())
            ->setCaseNumber($data['caseNumber'] ?? $caseNumber)
            ->setClientSurname($data['clientLastName'] ?? 'Smith')
            ->setDeputyUid($deputyNumber)
            ->setDeputyAddress1($data['deputyAddress1'] ?? 'Victoria Park')
            ->setDeputyAddress2($data['deputyAddress2'] ?? 'Fakeville')
            ->setDeputyAddress3($data['deputyAddress3'] ?? 'Pretendham')
            ->setDeputyAddress4($data['deputyAddress4'] ?? null)
            ->setDeputyAddress5($data['deputyAddress5'] ?? null)
            ->setDeputyPostcode($data['deputyPostCode'] ?? 'SW1')
            ->setDeputySurname($data['deputyLastName'] ?? 'Jones')
            ->setIsNdrEnabled(false)
            ->setOrderDate(new DateTime())
            ->setTypeOfReport($reportType ?? 'OPG102')
            ->setOrderType($data['orderType'] ?? 'PFA')
            ->setIsCoDeputy($data['createCoDeputy'] ?? false)
            ->setHybrid($data['hybrid'] ?? null);

        return $this->preRegistrationFactory->createFromDto($dto);
    }

    public function createCoDeputy(string $caseNumber, array $data): PreRegistration
    {
        $deputyUid = (string) mt_rand(1, 999999999);

        $dto = (new LayDeputyshipDto())
            ->setCaseNumber($caseNumber)
            ->setClientSurname('Smith')
            ->setOrderType($data['orderType'])
            ->setDeputyUid($deputyUid)
            ->setDeputyAddress1($data['deputyAddress1'] ?? '7 Colonnade Square')
            ->setDeputyAddress2($data['deputyAddress2'] ?? 'Middletown')
            ->setDeputyAddress3($data['deputyAddress3'] ?? null)
            ->setDeputyAddress4($data['deputyAddress4'] ?? null)
            ->setDeputyAddress5($data['deputyAddress5'] ?? null)
            ->setDeputyPostcode('SW1')
            ->setDeputyFirstname('Joe')
            ->setDeputySurname('Bloggs')
            ->setIsNdrEnabled(false)
            ->setOrderDate(new DateTime())
            ->setIsCoDeputy(true)
            ->setOrderType($data['orderType'] ?? 'PFA')
            ->setTypeOfReport($data['reportType'])
            ->setHybrid($data['hybrid'] ?? null);

        return $this->preRegistrationFactory->createFromDto($dto);
    }
}
