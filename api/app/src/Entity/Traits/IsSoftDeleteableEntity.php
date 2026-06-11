<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait IsSoftDeleteableEntity
{
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['client', 'transactionSoftDelete'])]
    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    protected ?\DateTime $deletedAt = null;

    public function setDeletedAt(?\DateTime $deletedAt = null): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
