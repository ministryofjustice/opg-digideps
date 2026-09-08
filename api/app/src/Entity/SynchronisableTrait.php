<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait SynchronisableTrait
{
    #[JMS\Type('string')]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\Column(name: 'synchronisation_status', type: 'string', nullable: true, options: ['default' => null])]
    protected ?string $synchronisationStatus = null;

    #[JMS\Type('DateTime')]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\Column(name: 'synchronisation_time', type: 'datetime', nullable: true, options: ['default' => null])]
    protected ?\DateTime $synchronisationTime = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\Column(name: 'synchronisation_error', type: 'text', length: 65535, nullable: true, options: ['default' => null])]
    protected ?string $synchronisationError = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\JoinColumn(name: 'synchronised_by', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected ?User $synchronisedBy = null;

    public function getSynchronisationStatus(): ?string
    {
        return $this->synchronisationStatus;
    }

    public function setSynchronisationStatus(?string $status): static
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

    public function setSynchronisationTime(?\DateTime $time): static
    {
        $this->synchronisationTime = $time;

        return $this;
    }

    public function getSynchronisationError(): ?string
    {
        return $this->synchronisationError;
    }

    public function setSynchronisationError(?string $error): static
    {
        $this->synchronisationError = $error;

        return $this;
    }

    public function getSynchronisedBy(): ?User
    {
        return $this->synchronisedBy;
    }

    public function setSynchronisedBy(?User $user): static
    {
        $this->synchronisedBy = $user;

        return $this;
    }
}
