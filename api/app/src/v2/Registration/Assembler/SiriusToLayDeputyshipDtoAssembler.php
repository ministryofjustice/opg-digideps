<?php

namespace App\v2\Registration\Assembler;

use App\v2\Registration\DTO\LayDeputyshipDto;

class SiriusToLayDeputyshipDtoAssembler implements LayDeputyshipDtoAssemblerInterface
{
    private array $requiredData = [
        'Case',
        'ClientSurname',
        'DeputyUid',
        'DeputyFirstname',
        'DeputySurname',
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

            throw new \InvalidArgumentException($message);
        }
        
        return $this->buildDto($data);
    }

    private function buildDto(array $data): LayDeputyshipDto
    {
        return
            (new LayDeputyshipDto())
                ->setCaseNumber($data['Case'])
                ->setClientFirstname($data['ClientFirstname'] ?: null)
                ->setClientSurname($data['ClientSurname'])
                ->setClientAddress1($data['ClientAddress1'] ?: null)
                ->setClientAddress2($data['ClientAddress2'] ?: null)
                ->setClientAddress3($data['ClientAddress3'] ?: null)
                ->setClientAddress4($data['ClientAddress4'] ?: null)
                ->setClientAddress5($data['ClientAddress5'] ?: null)
                ->setClientPostcode($data['ClientPostcode'] ?: null)
                ->setDeputyUid($data['DeputyUid'])
                ->setDeputyFirstname($data['DeputyFirstname'])
                ->setDeputySurname($data['DeputySurname'])
                ->setDeputyAddress1($data['DeputyAddress1'])
                ->setDeputyAddress2($data['DeputyAddress2'])
                ->setDeputyAddress3($data['DeputyAddress3'])
                ->setDeputyAddress4($data['DeputyAddress4'] ?: null)
                ->setDeputyAddress5($data['DeputyAddress5'] ?: null)
                ->setDeputyPostcode($data['DeputyPostcode'])
                ->setTypeOfReport($this->determineReportTypeIsSupported($data['ReportType']))
                ->setIsNdrEnabled(false)
                ->setOrderDate(new \DateTime($data['MadeDate']))
                ->setOrderType($data['OrderType'])
                ->setIsCoDeputy('yes' === $data['CoDeputy'])
                ->setHybrid($data['Hybrid']);
    }

    private function collectMissingColumns(array $data)
    {
        $this->missingColumns = [];
        foreach ($this->requiredData as $requiredColumn) {
            $this->missingColumns[] = array_key_exists($requiredColumn, $data) && !empty($data[$requiredColumn]) ? 
                null : 
                $requiredColumn;
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
            $message = sprintf(
                'Report type provided: %s is not supported ',
                $reportType
            );

            throw new \InvalidArgumentException($message);
        }

        return $reportType;
    }
}
