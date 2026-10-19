<?php

namespace OPG\Digideps\Backend\v2\DTO;

class StatusDto implements \JsonSerializable
{
    private string $status;

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
        ];
    }
}
