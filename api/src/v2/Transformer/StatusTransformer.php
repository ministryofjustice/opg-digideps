<?php

namespace App\v2\Transformer;

use App\v2\DTO\StatusDto;

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
