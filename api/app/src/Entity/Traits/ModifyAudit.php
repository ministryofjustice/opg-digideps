<?php

namespace OPG\Digideps\Backend\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use OPG\Digideps\Backend\Entity\User;

/**
 * ModifyAudit Trait, usable with PHP >= 5.4.
 */
trait ModifyAudit
{
    /**
     * Last modified by.
     *
     * @var User
     *
     * @JMS\Type("OPG\Digideps\Backend\Entity\User")
     *
     * @JMS\Groups({"last-modified"})
     *
     * @ORM\ManyToOne(targetEntity="OPG\Digideps\Backend\Entity\User", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $lastModifiedBy;

    /**
     * Last modified on.
     *
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     *
     * @JMS\Groups({"last-modified"})
     *
     * @ORM\Column(type="datetime", name="last_modified_on", nullable=true)
     *
     * @Gedmo\Timestampable(on="update")
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
     * @param User $lastModifiedBy
     *
     * @return $this
     */
    public function setLastModifiedBy($lastModifiedBy)
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
    public function setLastModifiedOn($lastModifiedOn)
    {
        $this->lastModifiedOn = $lastModifiedOn;

        return $this;
    }
}
