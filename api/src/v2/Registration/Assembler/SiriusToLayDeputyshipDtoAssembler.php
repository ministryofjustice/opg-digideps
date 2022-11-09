<?php

namespace App\v2\Registration\Assembler;

use App\v2\Registration\DTO\LayDeputyshipDto;
use DateTime;
use InvalidArgumentException;

class SiriusToLayDeputyshipDtoAssembler implements LayDeputyshipDtoAssemblerInterface
{
    private array $requiredColumns = [
        'Case',
        'ClientSurname',
        'DeputyUid',
        'DeputySurname',
        'DeputyAddress1',
        'DeputyAddress2',
        'DeputyAddress3',
        'DeputyAddress4',
        'DeputyAddress5',
        'DeputyPostcode',
        'ReportType',
        'MadeDate',
        'OrderType',
        'CoDeputy',
        'Hybrid',
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

        try {
            return $this->buildDto($data);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    private function buildDto(array $data): LayDeputyshipDto
    {
        return
            (new LayDeputyshipDto())
                ->setCaseNumber($data['Case'])
                ->setClientSurname($data['ClientSurname'])
                ->setDeputyUid($data['DeputyUid'])
                ->setDeputySurname($data['DeputySurname'])
                ->setDeputyAddress1($data['DeputyAddress1'])
                ->setDeputyAddress2($data['DeputyAddress2'])
                ->setDeputyAddress3($data['DeputyAddress3'])
                ->setDeputyAddress4($data['DeputyAddress4'])
                ->setDeputyAddress5($data['DeputyAddress5'])
                ->setDeputyPostcode($data['DeputyPostcode'])
                ->setTypeOfReport($this->determineReportTypeIsSupported($data['ReportType']))
                ->setIsNdrEnabled(false)
                ->setOrderDate(new DateTime($data['MadeDate']))
                ->setOrderType($data['OrderType'])
                ->setIsCoDeputy('yes' === $data['CoDeputy'])
                ->setHybrid($data['Hybrid']);
    }

    private function collectMissingColumns(array $data)
    {
        foreach ($this->requiredColumns as $requiredColumn) {
            $this->missingColumns[] = array_key_exists($requiredColumn, $data) ? null : $requiredColumn;
        }

        $this->missingColumns = array_filter($this->missingColumns);
    }

    private function determineReportTypeIsSupported(?string $reportType)
    {
        $supported = match ($reportType) {
            'OPG102', 'OPG103', 'OPG104' => true,
            default => false
        };

        if (!$supported) {
            throw new InvalidArgumentException();
        }

        return $reportType;
    }
}
