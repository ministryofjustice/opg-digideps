<?php

namespace OPG\Digideps\Frontend\Entity\Traits;

use JMS\Serializer\Annotation as JMS;

/**
 * SoftDeletable Trait, usable with PHP >= 5.4
 *
 */
trait IsSoftDeleteableEntity
{
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    protected ?\DateTime $deletedAt = null;

    /**
     * Sets deletedAt.
     */
    public function setDeletedAt(?\DateTime $deletedAt = null): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Returns deletedAt.
     *
     * @return \DateTime
     */
    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    /**
     * Is deleted?
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deletedAt !== null;
    }
}
