<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Traits;

use OPG\Digideps\Backend\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

trait CreationAudit
{
    /**
     * User who created the entity
     */
    #[JMS\Type(User::class)]
    #[JMS\Groups(['notes', 'documents', 'report-submission', 'checklist-information'])]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], fetch: 'EAGER')]
    protected ?User $createdBy = null;

    /**
     * The datetime when the entity was created
     */
    #[JMS\Type('DateTime')]
    #[JMS\Groups(['notes', 'documents', 'checklist-information'])]
    #[ORM\Column(name: 'created_on', type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'create')]
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
