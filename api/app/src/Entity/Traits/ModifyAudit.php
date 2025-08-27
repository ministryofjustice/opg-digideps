<?php

namespace App\Entity\Traits;

use App\Entity\User;
use DateTime;
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
     * @var User
     *
     * @JMS\Type("App\Entity\User")
     *
     * @JMS\Groups({"last-modified"})
     */
    #[ORM\JoinColumn(name: 'last_modified_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    protected $lastModifiedBy;

    /**
     * Last modified on.
     *
     * @var DateTime
     *
     * @JMS\Type("DateTime")
     *
     * @JMS\Groups({"last-modified"})
     *
     *
     * @Gedmo\Timestampable(on="update")
     */
    #[ORM\Column(type: 'datetime', name: 'last_modified_on', nullable: true)]
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
     * @return DateTime
     */
    public function getLastModifiedOn()
    {
        return $this->lastModifiedOn;
    }

    /**
     * @param DateTime $lastModifiedOn
     *
     * @return $this
     */
    public function setLastModifiedOn($lastModifiedOn)
    {
        $this->lastModifiedOn = $lastModifiedOn;

        return $this;
    }
}
