<?php

namespace OPG\Digideps\Backend\v2\DTO;

class StatusDto
{
    private string $status;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
