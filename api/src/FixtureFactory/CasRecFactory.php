<?php

declare(strict_types=1);

namespace App\FixtureFactory;

use App\Entity\CasRec;
use App\v2\Registration\DTO\LayDeputyshipDto;
use App\v2\Registration\SelfRegistration\Factory\CasRecFactory as CasRecDTOFactory;

class CasRecFactory
{
    /**
     * @var CasRecDTOFactory
     */
    private $casRecFactory;

    public function __construct(CasRecDTOFactory $casRecFactory)
    {
        $this->casRecFactory = $casRecFactory;
    }

    /**
     * @return Client
     */
    public function create(array $data): CasRec
    {
        $caseNumber = str_pad((string) rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $deputyNumber = str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $dto = (new LayDeputyshipDto())
            ->setCaseNumber($data['caseNumber'] ? $data['caseNumber'] : $caseNumber)
            ->setSource('casrec')
            ->setClientSurname($data['clientLastName'] ? $data['clientLastName'] : 'Smith')
            ->setCorref($this->determineCorref($data['reportType']))
            ->setDeputyNumber($deputyNumber)
            ->setDeputyPostcode($data['deputyPostCode'] ? $data['deputyPostCode'] : 'SW1')
            ->setDeputySurname($data['deputyLastName'] ? $data['deputyLastName'] : 'Jones')
            ->setIsNdrEnabled(false)
            ->setOrderDate(new \DateTime())
            ->setTypeOfReport($data['reportType']);

        return $this->casRecFactory->createFromDto($dto);
    }

    public function createCoDeputy(string $caseNumber, array $data): CasRec
    {
        $deputyNumber = str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $dto = (new LayDeputyshipDto())
            ->setCaseNumber($caseNumber)
            ->setSource('casrec')
            ->setClientSurname('Smith')
            ->setCorref($this->determineCorref($data['reportType']))
            ->setDeputyNumber($deputyNumber)
            ->setDeputyPostcode('SW1')
            ->setDeputySurname('Bloggs')
            ->setIsNdrEnabled(false)
            ->setOrderDate(new \DateTime())
            ->setTypeOfReport($data['reportType']);

        return $this->casRecFactory->createFromDto($dto);
    }

    private function determineCorref(string $reportType): string
    {
        switch ($reportType) {
            case '102':
                return 'l2';
            case '103':
                return 'l3';
            case '102-4':
            case '103-4':
            case '104':
                return 'hw';
            default:
                return 'l2';
        }
    }
}
