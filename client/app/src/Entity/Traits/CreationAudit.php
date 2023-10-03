<?php

namespace App\Entity\Traits;

use App\Entity\User;

/**
 * CreationAudit Trait, usable with PHP >= 5.4.
 */
trait CreationAudit
{
    /**
     * Created by.
     *
     * @JMS\Type("App\Entity\User")
     *
     * @var \App\Entity\User
     */
    protected $createdBy;

    /**
     * Created on.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $createdOn;

    /**
     * @return \App\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
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
     * @return $this
     */
    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }
}
