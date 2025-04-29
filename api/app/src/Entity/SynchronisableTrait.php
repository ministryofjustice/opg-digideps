<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use JMS\Serializer\Annotation as JMS;

trait SynchronisableTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="synchronisation_status", type="string", options={"default": null}, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['synchronisation'])]
    protected $synchronisationStatus;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="synchronisation_time", type="datetime", options={"default": null}, nullable=true)
     */
    #[JMS\Type('DateTime')]
    #[JMS\Groups(['synchronisation'])]
    protected $synchronisationTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="synchronisation_error", type="text", length=65535, options={"default": null}, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['synchronisation'])]
    protected $synchronisationError;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     *
     * @ORM\JoinColumn(name="synchronised_by", referencedColumnName="id", onDelete="SET NULL")
     */
    #[JMS\Type('App\Entity\User')]
    #[JMS\Groups(['synchronisation'])]
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

    public function getSynchronisationTime(): ?DateTime
    {
        return $this->synchronisationTime;
    }

    /**
     * @return $this
     */
    public function setSynchronisationTime(?DateTime $time)
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
