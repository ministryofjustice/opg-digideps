<?php

namespace AppBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $lastModifiedBy;

    /**
     * Last modified on
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="last_modified_on", nullable=true)
     * @Gedmo\Timestampable(on="update")
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
