<?php

namespace App\v2\Transformer;

use App\v2\DTO\ReportDto;
use App\v2\DTO\StatusDto;

class ReportTransformer
{
    public function __construct(private readonly StatusTransformer $statusTransformer)
    {
    }

    /**
     * @return array
     */
    public function transform(ReportDto $dto)
    {
        $transformed = [
            'id' => $dto->getId(),
            'submitted' => $dto->getSubmitted(),
            'type' => $dto->getType(),
            'due_date' => $this->transformDate($dto, 'dueDate', 'Y-m-d'),
            'submit_date' => $this->transformDate($dto, 'submitDate', 'Y-m-d\TH:i:sP'),
            'un_submit_date' => $this->transformDate($dto, 'unSubmitDate', 'Y-m-d'),
            'start_date' => $this->transformDate($dto, 'startDate', 'Y-m-d'),
            'end_date' => $this->transformDate($dto, 'endDate', 'Y-m-d'),
        ];

        if (null !== $dto->getAvailableSections()) {
            $transformed['available_sections'] = $dto->getAvailableSections();
        }

        if ($dto->getStatus() instanceof StatusDto) {
            $transformed['status'] = $this->statusTransformer->transform($dto->getStatus());
        }

        return $transformed;
    }

    private function transformDate(ReportDto $dto, $property, $format)
    {
        $getter = sprintf('get%s', ucfirst($property));

        return $dto->{$getter}() instanceof \DateTime ? $dto->{$getter}()->format($format) : null;
    }
}
