<?php

namespace OPG\Digideps\Backend\v2\DTO;

class StatusDto
{
    public string $status;

    public function asArray(): array
    {
        return [
            'status' => $this->status,
        ];
    }
}
