<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * SoftDeletable Trait, usable with PHP >= 5.4.
 */
trait IsSoftDeleteableEntity
{
    /**
     * @var \DateTime
     */
    #[JMS\Type("DateTime<'Y-m-d H:i:s'>")]
    #[JMS\Groups(['client', 'transactionSoftDelete'])]
    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    protected $deletedAt;

    /**
     * Sets deletedAt.
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
        return null !== $this->deletedAt;
    }
}
