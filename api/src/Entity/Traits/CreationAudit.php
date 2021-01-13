<?php

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CreationAudit Trait, usable with PHP >= 5.4
 *
 */
trait CreationAudit
{
    /**
     * Created by
     *
     * @var \App\Entity\User
     *
     * @JMS\Type("App\Entity\User")
     * @JMS\Groups({"notes", "documents", "report-submission", "checklist-information"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $createdBy;

    /**
     * Created on
     *
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     * @JMS\Groups({"notes", "documents", "checklist-information"})
     * @ORM\Column(type="datetime", name="created_on", nullable=true)
     * @Gedmo\Timestampable(on="create")
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
     * @param \App\Entity\User $createdBy
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
