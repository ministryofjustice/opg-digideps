<?php

namespace App\v2\Registration\Assembler;

use App\Entity\CasRec;
use App\Service\DataNormaliser;
use App\v2\Registration\DTO\LayDeputyshipDto;

class CasRecToLayDeputyshipDtoAssembler implements LayDeputyshipDtoAssemblerInterface
{
    private array $requiredColumns = [
        'Case',
        'Surname',
        'Deputy No',
        'Dep Surname',
        'Dep Postcode',
        'Typeofrep',
        'Corref',
        'NDR',
        'Made Date',
    ];

    private array $missingColumns = [];

    /**
     * @return LayDeputyshipDto
     */
    public function assembleFromArray(array $data)
    {
        $this->collectMissingColumns($data);

        if (!empty($this->missingColumns)) {
            $message = sprintf(
                'Cannot assemble LayDeputyshipDto. Missing columns in CSV: %s ',
                implode(', ', $this->missingColumns)
            );

            throw new \InvalidArgumentException($message);
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

    private function collectMissingColumns(array $data)
    {
        foreach ($this->requiredColumns as $requiredColumn) {
            $this->missingColumns[] = array_key_exists($requiredColumn, $data) ? null : $requiredColumn;
        }

        $this->missingColumns = array_filter($this->missingColumns);
    }

    /**
     * @param $value
     */
    private function determineNdrStatus($value): bool
    {
        return (1 === $value || 'Y' === $value) ? true : false;
    }
}
