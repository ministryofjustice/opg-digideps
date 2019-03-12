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
            'submitDate' => $dto->getSubmitDate()->format('Y-m-d')
        ];
    }
}
