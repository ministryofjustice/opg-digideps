<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\User;

trait ModifyAudit
{
    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[JMS\Groups(['last-modified'])]
    #[ORM\JoinColumn(name: 'last_modified_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    protected ?User $lastModifiedBy = null;

    #[JMS\Type('DateTime')]
    #[JMS\Groups(['last-modified'])]
    #[ORM\Column(name: 'last_modified_on', type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    protected ?\DateTime $lastModifiedOn = null;

    public function getLastModifiedBy(): ?User
    {
        return $this->lastModifiedBy;
    }

    public function setLastModifiedBy(?User $lastModifiedBy): static
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    public function getLastModifiedOn(): ?\DateTime
    {
        return $this->lastModifiedOn;
    }


    public function setLastModifiedOn(?\DateTime $lastModifiedOn): static
    {
        $this->lastModifiedOn = $lastModifiedOn;

        return $this;
    }
}
