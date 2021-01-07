<?php

namespace App\v2\Registration\Assembler;

use App\Entity\CasRec;
use App\Service\DataNormaliser;
use App\v2\Registration\DTO\LayDeputyshipDto;

class CasRecToLayDeputyshipDtoAssembler implements LayDeputyshipDtoAssemblerInterface
{
    /**
     * @param array $data
     * @return LayDeputyshipDto
     */
    public function assembleFromArray(array $data)
    {
        if (!$this->canAssemble($data)) {
            throw new \InvalidArgumentException('Cannot assemble LayDeputyshipDto: Missing expected data');
        }

        return
            (new LayDeputyshipDto())
                ->setCaseNumber(DataNormaliser::normaliseCaseNumber($data['Case']))
                ->setClientSurname(DataNormaliser::normaliseSurname($data['Surname']))
                ->setDeputyNumber(DataNormaliser::normaliseDeputyNo($data['Deputy No']))
                ->setDeputySurname(DataNormaliser::normaliseSurname($data['Dep Surname']))
                ->setDeputyPostcode(DataNormaliser::normalisePostCode($data['Dep Postcode']))
                ->setTypeOfReport($data['Typeofrep'])
                ->setCorref($data['Corref'])
                ->setIsNdrEnabled($this->determineNdrStatus($data['NDR']))
                ->setSource(CasRec::CASREC_SOURCE)
                ->setOrderDate(new \DateTime($data['Made Date']));
    }

    /**
     * @param array $data
     * @return bool
     */
    private function canAssemble(array $data)
    {
        return
            array_key_exists('Case', $data) &&
            array_key_exists('Surname', $data) &&
            array_key_exists('Deputy No', $data) &&
            array_key_exists('Dep Surname', $data) &&
            array_key_exists('Dep Postcode', $data) &&
            array_key_exists('Typeofrep', $data) &&
            array_key_exists('Corref', $data) &&
            array_key_exists('NDR', $data) &&
            array_key_exists('Made Date', $data);
    }

    /**
     * @param $value
     * @return bool
     */
    private function determineNdrStatus($value): bool
    {
        return ($value === 1 || $value === 'Y') ? true : false;
    }
}
