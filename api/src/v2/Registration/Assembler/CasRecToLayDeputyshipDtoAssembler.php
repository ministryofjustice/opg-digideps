<?php

namespace App\v2\Registration\Assembler;

use App\v2\Registration\DTO\LayDeputyshipDto;
use DateTime;
use InvalidArgumentException;

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

            throw new InvalidArgumentException($message);
        }

        return
            (new LayDeputyshipDto())
                ->setCaseNumber($data['Case'])
                ->setClientSurname($data['Surname'])
                ->setDeputyUid($data['Deputy No'])
                ->setDeputySurname($data['Dep Surname'])
                ->setDeputyPostcode($data['Dep Postcode'])
                ->setTypeOfReport($data['Typeofrep'])
                ->setCorref($data['Corref'])
                ->setIsNdrEnabled($this->determineNdrStatus($data['NDR']))
                ->setOrderDate(new DateTime($data['Made Date']));
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
