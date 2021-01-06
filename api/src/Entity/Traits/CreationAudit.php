<?php

namespace AppBundle\Entity\Traits;

use AppBundle\Entity\User;
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
     * @var \AppBundle\Entity\User
     *
     * @JMS\Type("AppBundle\Entity\User")
     * @JMS\Groups({"notes", "documents", "report-submission", "checklist-information"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", fetch="EAGER")
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
