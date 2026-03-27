<?php

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait CreationAudit
{
    /**
     * User who created the entity
     *
     * @JMS\Type("App\Entity\User")
     * @JMS\Groups({"notes", "documents", "report-submission", "checklist-information"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected ?User $createdBy = null;

    /**
     * The datetime when the entity was created
     *
     * @JMS\Type("DateTime")
     * @JMS\Groups({"notes", "documents", "checklist-information"})
     *
     * @ORM\Column(type="datetime", name="created_on", nullable=true)
     *
     * @Gedmo\Timestampable(on="create")
     */
    protected ?\DateTime $createdOn = null;

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedOn(): ?\DateTime
    {
        return $this->createdOn;
    }

    public function setCreatedOn(?\DateTime $createdOn): static
    {
        $this->createdOn = $createdOn;

        return $this;
    }
}
