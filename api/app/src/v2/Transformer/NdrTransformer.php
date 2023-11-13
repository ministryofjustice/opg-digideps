<?php

namespace App\v2\Transformer;

use App\v2\DTO\NdrDto;

class NdrTransformer
{
    /**
     * @return array
     */
    public function transform(NdrDto $dto)
    {
        return [
            'id' => $dto->getId(),
            'submitted' => $dto->getSubmitted(),
            'submit_date' => $this->transformDate($dto, 'submitDate', 'Y-m-d\TH:i:sP'),
            'start_date' => $this->transformDate($dto, 'startDate', 'Y-m-d\TH:i:sP'),
        ];
    }

    private function transformDate(NdrDto $dto, $property, $format)
    {
        $getter = sprintf('get%s', ucfirst($property));

        return $dto->{$getter}() instanceof \DateTime ? $dto->{$getter}()->format($format) : null;
    }
}
