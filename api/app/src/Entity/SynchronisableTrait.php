<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait SynchronisableTrait
{
    /**
     * @var ?string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\Column(name: 'synchronisation_status', type: 'string', nullable: true, options: ['default' => null])]
    protected $synchronisationStatus;

    /**
     * @var ?\DateTime
     */
    #[JMS\Type(\DateTime::class)]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\Column(name: 'synchronisation_time', type: 'datetime', nullable: true, options: ['default' => null])]
    protected $synchronisationTime;

    /**
     * @var ?string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\Column(name: 'synchronisation_error', type: 'text', length: 65535, nullable: true, options: ['default' => null])]
    protected $synchronisationError;

    /**
     * @var ?User
     */
    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\JoinColumn(name: 'synchronised_by', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected $synchronisedBy;

    public function getSynchronisationStatus(): ?string
    {
        return $this->synchronisationStatus;
    }

    /**
     * @return $this
     */
    public function setSynchronisationStatus(?string $status)
    {
        if (
            !in_array($status, [
            self::SYNC_STATUS_QUEUED,
            self::SYNC_STATUS_IN_PROGRESS,
            self::SYNC_STATUS_SUCCESS,
            self::SYNC_STATUS_TEMPORARY_ERROR,
            self::SYNC_STATUS_PERMANENT_ERROR,
            ])
        ) {
            throw new \InvalidArgumentException('Invalid synchronisation status');
        }

        $this->synchronisationStatus = $status;

        return $this;
    }

    public function getSynchronisationTime(): ?\DateTime
    {
        return $this->synchronisationTime;
    }

    /**
     * @return $this
     */
    public function setSynchronisationTime(?\DateTime $time)
    {
        $this->synchronisationTime = $time;

        return $this;
    }

    public function getSynchronisationError(): ?string
    {
        return $this->synchronisationError;
    }

    /**
     * @return $this
     */
    public function setSynchronisationError(?string $error)
    {
        $this->synchronisationError = $error;

        return $this;
    }

    public function getSynchronisedBy(): ?User
    {
        return $this->synchronisedBy;
    }

    /**
     * @return $this
     */
    public function setSynchronisedBy(?User $user)
    {
        $this->synchronisedBy = $user;

        return $this;
    }
}
