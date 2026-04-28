<?php

namespace OPG\Digideps\Backend\v2\Transformer;

use OPG\Digideps\Backend\v2\DTO\StatusDto;

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
