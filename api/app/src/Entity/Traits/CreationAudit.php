<?php

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * CreationAudit Trait, usable with PHP >= 5.4.
 */
trait CreationAudit
{
    /**
     * Created by.
     *
     * @var \App\Entity\User
     *
     *
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    #[JMS\Type('App\Entity\User')]
    #[JMS\Groups(['notes', 'documents', 'report-submission', 'checklist-information'])]
    protected $createdBy;

    /**
     * Created on.
     *
     * @var \DateTime
     *
     *
     *
     * @ORM\Column(type="datetime", name="created_on", nullable=true)
     *
     * @Gedmo\Timestampable(on="create")
     */
    #[JMS\Type('DateTime')]
    #[JMS\Groups(['notes', 'documents', 'checklist-information'])]
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
