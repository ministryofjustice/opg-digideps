<?php

namespace App\v2\Registration\Assembler;

use App\Entity\CasRec;
use App\Service\DataNormaliser;
use App\v2\Registration\DTO\LayDeputyshipDto;

class SiriusToLayDeputyshipDtoAssembler implements LayDeputyshipDtoAssemblerInterface
{
    private array $requiredColumns = [
        'Case',
        'Surname',
        'Deputy No',
        'Dep Surname',
        'Dep Postcode',
        'Typeofrep',
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

        try {
            return $this->buildDto($data);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function buildDto(array $data): LayDeputyshipDto
    {
        return
            (new LayDeputyshipDto())
                ->setCaseNumber(DataNormaliser::normaliseCaseNumber($data['Case']))
                ->setClientSurname(DataNormaliser::normaliseSurname($data['Surname']))
                ->setDeputyNumber(DataNormaliser::normaliseDeputyNo($data['Deputy No']))
                ->setDeputySurname(DataNormaliser::normaliseSurname($data['Dep Surname']))
                ->setDeputyPostcode(DataNormaliser::normalisePostCode($data['Dep Postcode']))
                ->setTypeOfReport($data['Typeofrep'])
                ->setCorref($this->determineCorref($data['Typeofrep']))
                ->setIsNdrEnabled(false)
                ->setSource(CasRec::SIRIUS_SOURCE)
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
            array_key_exists('Made Date', $data);
    }

    private function determineCorref(string $reportType): string
    {
        switch ($reportType) {
            case 'OPG102':
                return 'L2';
            case 'OPG103':
                return 'L3';
            default:
                throw new \InvalidArgumentException('Cannot assemble LayDeputyshipDto: Unexpected report type');
        }
    }
}
