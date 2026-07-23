<?php

namespace OPG\Digideps\Frontend\Entity\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\User;

/**
 * CreationAudit Trait, usable with PHP >= 5.4.
 */
trait CreationAudit
{
    /**
     * Created by.
     *
     *
     * @var User
     */
    #[JMS\Type('OPG\Digideps\Frontend\Entity\User')]
    protected $createdBy;

    /**
     * Created on.
     *
     *
     * @var \DateTime
     */
    #[JMS\Type('DateTime')]
    protected $createdOn;

    /**
     * @return User
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
