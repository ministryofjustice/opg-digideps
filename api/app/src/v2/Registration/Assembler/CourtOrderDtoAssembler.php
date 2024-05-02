<?php

declare(strict_types=1);

namespace App\v2\Registration\Assembler;

use App\v2\Registration\DTO\CourtOrderDto;
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

    public function assembleFromLayDto(LayDeputyshipDto $layDeputyshipDto): CourtOrderDto
    {
        $courtOrderDto = [
            'CourtOrderUid' => $layDeputyshipDto->getCourtOrderUid(),
            'Type' => $layDeputyshipDto->getHybrid(),
            'Active' => true,
        ];

        $this->collectMissingColumns($courtOrderDto);

        return $this->buildDto($courtOrderDto);
    }

    private function buildDto(array $data): CourtOrderDto
    {
        return
            (new CourtOrderDto())
                ->setOrderUid($data['CourtOrderUid'])
                ->setOrderType($this->determineCourtOrderTypeIsSupported($data['Type']))
                ->setOrderActive($data['Active']);
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

    private function determineCourtOrderTypeIsSupported(?string $reportType): string
    {
        $supported = match ($reportType) {
            'SINGLE', 'DUAL', 'HYBRID' => true,
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
