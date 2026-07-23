<?php

namespace OPG\Digideps\Frontend\Entity\Traits;

use JMS\Serializer\Annotation as JMS;

/**
 * SoftDeletable Trait, usable with PHP >= 5.4
 *
 */
trait IsSoftDeleteableEntity
{
    /**
     * @var \DateTime
     */
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    protected $deletedAt;

    /**
     * Sets deletedAt.
     *
     * @param \Datetime|null $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(?\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Returns deletedAt.
     *
     * @return \DateTime
     */
    public function getDeletedAt()
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
