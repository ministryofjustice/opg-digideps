<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\User;

trait ModifyAudit
{
    /**
     * Last modified by.
     *
     * @var User
     */
    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[JMS\Groups(['last-modified'])]
    #[ORM\JoinColumn(name: 'last_modified_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    protected $lastModifiedBy;

    /**
     * Last modified on.
     *
     * @var \DateTime
     */
    #[JMS\Type('DateTime')]
    #[JMS\Groups(['last-modified'])]
    #[ORM\Column(name: 'last_modified_on', type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
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
