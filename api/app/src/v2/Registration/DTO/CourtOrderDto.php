<?php

declare(strict_types=1);

namespace App\v2\Registration\DTO;

class CourtOrderDto
{
    private ?int $courtOrderUid;
    private ?string $type;
    private ?bool $active;

    public function getOrderUid(): ?int
    {
        return $this->courtOrderUid;
    }

    public function setOrderUid(?int $courtOrderUid): CourtOrderDto
    {
        $this->courtOrderUid = $courtOrderUid;
        
        return $this;
    }

    public function getOrderType(): ?string
    {
        return $this->type;
    }

    public function setOrderType(?string $type): CourtOrderDto
    {
        $this->type = $type;
        
        return $this;
    }

    public function getOrderActive(): ?bool
    {
        return $this->active;
    }

    public function setOrderActive(?string $active): CourtOrderDto
    {
        $this->active = $active;
        
        return $this;
    }
}
