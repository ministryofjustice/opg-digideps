<?php

namespace OPG\Digideps\Frontend\Entity\Traits;

use OPG\Digideps\Frontend\Entity\User;

/**
 * ModifyAudit Trait, usable with PHP >= 5.4.
 */
trait ModifyAudit
{
    /**
     * Last modified by.
     *
     * @JMS\Groups({"last-modified"})
     * @JMS\Type("OPG\Digideps\Frontend\Entity\User")
     *
     * @var User
     */
    protected $lastModifiedBy;

    /**
     * Last modified on.
     *
     * @JMS\Groups({"last-modified"})
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $lastModifiedOn;

    /**
     * @return User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * @return $this
     */
    public function setLastModifiedBy(User $lastModifiedBy)
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

    /**
     * @return $this
     */
    public function setLastModifiedOn(\DateTime $lastModifiedOn)
    {
        $this->lastModifiedOn = $lastModifiedOn;

        return $this;
    }
}
