<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ModifyAudit Trait, usable with PHP >= 5.4.
 */
trait ModifyAudit
{
    /**
     * Last modified by.
     *
     * @var \App\Entity\User
     *
     * @JMS\Type("App\Entity\User")
     *
     * @JMS\Groups({"last-modified"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
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
     * @return \App\Entity\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * @param \App\Entity\User $lastModifiedBy
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
