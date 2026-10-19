<?php

namespace OPG\Digideps\Frontend\Entity\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\User;

/**
 * ModifyAudit Trait, usable with PHP >= 5.4.
 */
trait ModifyAudit
{
    /**
     * Last modified by.
     * @var User
     */
    #[JMS\Groups(['last-modified'])]
    #[JMS\Type('OPG\Digideps\Frontend\Entity\User')]
    protected $lastModifiedBy;

    /**
     * Last modified on.
     * @var \DateTime
     */
    #[JMS\Groups(['last-modified'])]
    #[JMS\Type('DateTime')]
    protected $lastModifiedOn;

    /**
     * @return User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    public function setLastModifiedBy(User $lastModifiedBy): static
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastModifiedOn()
    {
        return $this->lastModifiedOn;
    }

    public function setLastModifiedOn(\DateTime $lastModifiedOn): static
    {
        $this->lastModifiedOn = $lastModifiedOn;

        return $this;
    }
}
