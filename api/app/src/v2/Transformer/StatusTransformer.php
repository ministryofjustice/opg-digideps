<?php

namespace App\v2\Transformer;

use App\v2\DTO\StatusDto;

class StatusTransformer
{
    /**
     * @return array
     */
    public function transform(StatusDto $dto)
    {
        return [
            'status' => $dto->getStatus(),
        ];
    }
}
