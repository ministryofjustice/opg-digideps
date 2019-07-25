<?php

namespace AppBundle\Entity\Traits;

use AppBundle\Entity\User;

/**
 * CreationAudit Trait, usable with PHP >= 5.4
 *
 */
trait CreationAudit
{
    /**
     * Created by
     *
     * @JMS\Type("AppBundle\Entity\User")
     * @var \AppBundle\Entity\User
     *
     */
    protected $createdBy;

    /**
     * Created on
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     *
     */
    protected $createdOn;

    /**
     * @return \AppBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param \AppBundle\Entity\User $createdBy
     *
     * @return $this
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTime $createdOn
     *
     * @return $this
     */
    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }
}
