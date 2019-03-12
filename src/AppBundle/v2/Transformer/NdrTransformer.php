<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\NdrDto;

class NdrTransformer
{
    /**
     * @param NdrDto $dto
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

    /**
     * @param NdrDto $dto
     * @param $property
     * @return null
     */
    private function transformDate(NdrDto $dto, $property, $format)
    {
        $getter = sprintf('get%s', ucfirst($property));

        return $dto->{$getter}() instanceof \DateTime ? $dto->{$getter}()->format($format) : null;
    }

}
