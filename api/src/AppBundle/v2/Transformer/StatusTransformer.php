<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\StatusDto;

class StatusTransformer
{
    /**
     * @param StatusDto $dto
     * @return array
     */
    public function transform(StatusDto $dto)
    {
        return [
            'status' => $dto->getStatus()
        ];
    }
}
