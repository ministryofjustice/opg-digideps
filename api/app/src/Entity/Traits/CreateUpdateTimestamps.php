<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait CreateUpdateTimestamps
{
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt = null;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $timestamp): static
    {
        $this->createdAt = $timestamp;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $timestamp): static
    {
        $this->updatedAt = $timestamp;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtAutomatically(): void
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime());
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtAutomatically(): void
    {
        $this->setUpdatedAt(new \DateTime());
    }
}
