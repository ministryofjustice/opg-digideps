<?php

namespace AppBundle\Entity\Traits;

use AppBundle\Entity\User;

/**
 * ModifyAudit Trait, usable with PHP >= 5.4
 *
 */
trait ModifyAudit
{
    /**
     * Last modified by
     *
     * @var \AppBundle\Entity\User
     *
     */
    protected $lastModifiedBy;

    /**
     * Last modified on
     *
     * @var \DateTime
     *
     */
    protected $lastModifiedOn;

    /**
     * @return \AppBundle\Entity\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * @param \AppBundle\Entity\User $lastModifiedBy
     *
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
     * @param \DateTime $lastModifiedOn
     *
     * @return $this
     */
    public function setLastModifiedOn(\DateTime $lastModifiedOn)
    {
        $this->lastModifiedOn = $lastModifiedOn;
        return $this;
    }
}
