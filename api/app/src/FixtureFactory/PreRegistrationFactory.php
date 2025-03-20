<?php

declare(strict_types=1);

namespace App\FixtureFactory;

use App\Entity\PreRegistration;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\SelfRegistration\Factory\PreRegistrationFactory as PreRegistrationDTOFactory;

class PreRegistrationFactory
{
    public function __construct(private readonly PreRegistrationDTOFactory $preRegistrationFactory)
    {
    }

    public function create(array $data): PreRegistration
    {
        $caseNumber = str_pad((string) rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $generateDeputyUidIfNotSet = '7'.str_pad((string) rand(1, 99999999), 11, '0', STR_PAD_LEFT);
        $deputyUid = array_key_exists('deputyUid', $data) && strval($data['deputyUid']) ? strval($data['deputyUid']) : $generateDeputyUidIfNotSet;
        $reportType = 'ndr' == $data['reportType'] ? 'OPG102' : $data['reportType'];

        $dto = (new LayDeputyshipDto())
            ->setCaseNumber($data['caseNumber'] ?? $caseNumber)
            ->setClientFirstname($data['clientFirstName'] ?? 'John')
            ->setClientSurname($data['clientLastName'] ?? 'Smith')
            ->setDeputyUid($deputyUid)
            ->setClientAddress1($data['clientAddress1'] ?? 'Client Street')
            ->setClientAddress2($data['clientAddress2'] ?? 'Clientville')
            ->setClientAddress3($data['clientAddress3'] ?? 'ClientTon')
            ->setClientAddress4($data['clientAddress4'] ?? null)
            ->setClientAddress5($data['clientAddress5'] ?? null)
            ->setClientPostcode($data['clientPostCode'] ?? 'DY8')
            ->setDeputyAddress1($data['deputyAddress1'] ?? 'Victoria Park')
            ->setDeputyAddress2($data['deputyAddress2'] ?? 'Fakeville')
            ->setDeputyAddress3($data['deputyAddress3'] ?? 'Pretendham')
            ->setDeputyAddress4($data['deputyAddress4'] ?? null)
            ->setDeputyAddress5($data['deputyAddress5'] ?? null)
            ->setDeputyPostcode($data['deputyPostCode'] ?? 'SW1')
            ->setDeputyFirstname($data['deputyFirstname'] ?? 'Mel')
            ->setDeputySurname($data['deputyLastName'] ?? 'Jones')
            ->setIsNdrEnabled(false)
            ->setOrderDate(new \DateTime())
            ->setTypeOfReport($reportType ?? 'OPG102')
            ->setOrderType($data['orderType'] ?? 'PFA')
            ->setIsCoDeputy($data['createCoDeputy'] ?? false)
            ->setHybrid($data['hybrid'] ?? null);

        return $this->preRegistrationFactory->createFromDto($dto);
    }

    public function createCoDeputy(string $caseNumber, array $data): PreRegistration
    {
        $deputyUid = str_pad((string) rand(1, 999999999999), 12, '0', STR_PAD_LEFT);

        $dto = (new LayDeputyshipDto())
            ->setCaseNumber($caseNumber)
            ->setClientFirstname('John')
            ->setClientSurname('Smith')
            ->setOrderType($data['orderType'])
            ->setDeputyUid($deputyUid)
            ->setClientAddress1('Client Street')
            ->setClientAddress2('Clientville')
            ->setClientAddress3('ClientTon')
            ->setClientAddress4(null)
            ->setClientAddress5(null)
            ->setClientPostcode('DY8')
            ->setDeputyAddress1($data['deputyAddress1'] ?? '7 Colonnade Square')
            ->setDeputyAddress2($data['deputyAddress2'] ?? 'Middletown')
            ->setDeputyAddress3($data['deputyAddress3'] ?? null)
            ->setDeputyAddress4($data['deputyAddress4'] ?? null)
            ->setDeputyAddress5($data['deputyAddress5'] ?? null)
            ->setDeputyPostcode('SW1')
            ->setDeputyFirstname('Jamie')
            ->setDeputySurname('Bloggs')
            ->setIsNdrEnabled(false)
            ->setOrderDate(new \DateTime())
            ->setIsCoDeputy(true)
            ->setOrderType($data['orderType'] ?? 'PFA')
            ->setTypeOfReport($data['reportType'])
            ->setHybrid($data['hybrid'] ?? null);

        return $this->preRegistrationFactory->createFromDto($dto);
    }
}
