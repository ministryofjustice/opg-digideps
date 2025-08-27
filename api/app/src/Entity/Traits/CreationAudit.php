<?php

namespace App\Entity\Traits;

use DateTime;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CreationAudit Trait, usable with PHP >= 5.4.
 */
trait CreationAudit
{
    /**
     * Created by.
     *
     * @var User
     *
     * @JMS\Type("App\Entity\User")
     *
     * @JMS\Groups({"notes", "documents", "report-submission", "checklist-information"})
     */
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    protected $createdBy;

    /**
     * Created on.
     *
     * @var DateTime
     *
     * @JMS\Type("DateTime")
     *
     * @JMS\Groups({"notes", "documents", "checklist-information"})
     *
     *
     * @Gedmo\Timestampable(on="create")
     */
    #[ORM\Column(type: 'datetime', name: 'created_on', nullable: true)]
    protected $createdOn;

    /**
     * @return User
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
     * @return DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @return $this
     */
    public function setCreatedOn(DateTime $createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }
}
