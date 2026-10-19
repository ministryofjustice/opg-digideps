<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\Assembler;

use OPG\Digideps\Backend\v2\Registration\DTO\LayDeputyshipDto;

class SiriusToLayDeputyshipDtoAssembler implements LayDeputyshipDtoAssemblerInterface
{
    /** @var String[] $requiredData  */
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

    /** @var array<string> $missingColumns  */
    private array $missingColumns = [];

    public function assembleFromArray(array $data): LayDeputyshipDto
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
            new LayDeputyshipDto()
                ->setCaseNumber($data['Case'] ?? null)
                ->setClientFirstname($data['ClientFirstname'] ?? null)
                ->setClientSurname($data['ClientSurname'] ?? null)
                ->setClientAddress1($data['ClientAddress1'] ?? null)
                ->setClientAddress2($data['ClientAddress2'] ?? null)
                ->setClientAddress3($data['ClientAddress3'] ?? null)
                ->setClientAddress4($data['ClientAddress4'] ?? null)
                ->setClientAddress5($data['ClientAddress5'] ?? null)
                ->setClientPostcode($data['ClientPostcode'] ?? null)
                ->setDeputyUid($data['DeputyUid'] ?? null)
                ->setDeputyFirstname($data['DeputyFirstname'] ?? null)
                ->setDeputySurname($data['DeputySurname'] ?? null)
                ->setDeputyAddress1($data['DeputyAddress1'] ?? null)
                ->setDeputyAddress2($data['DeputyAddress2'] ?? null)
                ->setDeputyAddress3($data['DeputyAddress3'] ?? null)
                ->setDeputyAddress4($data['DeputyAddress4'] ?? null)
                ->setDeputyAddress5($data['DeputyAddress5'] ?? null)
                ->setDeputyPostcode($data['DeputyPostcode'] ?? null)
                ->setTypeOfReport($this->determineReportTypeIsSupported($data['ReportType'] ?? null))
                ->setOrderDate(new \DateTime($data['MadeDate']))
                ->setOrderType($data['OrderType'])
                ->setIsCoDeputy($data['CoDeputy'] === 'yes')
                ->setHybrid($data['Hybrid']);
    }

    /** @param Mixed[] $data */
    private function collectMissingColumns(array $data): void
    {
        $this->missingColumns = [];
        foreach ($this->requiredData as $requiredColumn) {
            if (array_key_exists($requiredColumn, $data) && empty($data[$requiredColumn])) {
                $this->missingColumns[] = $requiredColumn;
            }
        }

        $this->missingColumns = array_filter($this->missingColumns);
    }

    private function determineReportTypeIsSupported(?string $reportType): string
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
