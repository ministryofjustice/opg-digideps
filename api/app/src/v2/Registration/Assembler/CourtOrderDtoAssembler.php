<?php

declare(strict_types=1);

namespace App\v2\Registration\Assembler;

use App\v2\Registration\DTO\LayDeputyshipDto;

class CourtOrderDtoAssembler
{
    private array $requiredData = [
        'CourtOrderUid',
        'Type',
        'Active',
    ];

    private array $missingColumns = [];
    
    public function assembleFromArray(array $data): CourtOrderDto
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

    private function buildDto(array $data): CourtOrderDto
    {
        return
            (new CourtOrderDto())
                ->setOrderUid($data['CourtOrderUid'])
                ->setTypeOfOrder($this->determineOrderTypeIsSupported($data['Type']))
                ->setActive($data['Active']);
    }

    private function collectMissingColumns(array $data): void
    {
        $this->missingColumns = [];
        foreach ($this->requiredData as $requiredColumn) {
            $this->missingColumns[] = array_key_exists($requiredColumn, $data) && !empty($data[$requiredColumn]) ?
                null :
                $requiredColumn;
        }

        $this->missingColumns = array_filter($this->missingColumns);
    }

    private function determineOrderTypeIsSupported(?string $reportType): string
    {
        $supported = match ($reportType) {
            'HW', 'PFA', 'DUAL', 'HYBRID' => true,
            default => false
        };

        if (!$supported) {
            $message = sprintf(
                'Order type provided: %s is not supported ',
                $reportType
            );

            throw new \InvalidArgumentException($message);
        }

        return $reportType;
    }
}
