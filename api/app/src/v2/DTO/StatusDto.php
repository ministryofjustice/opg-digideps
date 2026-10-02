<?php

namespace OPG\Digideps\Backend\v2\DTO;

class StatusDto
{
    private string $status;

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function asArray(): array
    {
        return [
            'status' => $this->status,
        ];
    }
}
