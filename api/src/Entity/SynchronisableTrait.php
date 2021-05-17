<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Entity;

use DateTime;

trait SynchronisableTrait
{
    /**
     * @var string|null
     * @JMS\Type("string")
     * @JMS\Groups({"synchronisation"})
     * @ORM\Column(name="synchronisation_status", type="string", options={"default": null}, nullable=true)
     */
    protected $synchronisationStatus;

    /**
     * @var DateTime|null
     * @JMS\Type("DateTime")
     * @JMS\Groups({"synchronisation"})
     * @ORM\Column(name="synchronisation_time", type="datetime", options={"default": null}, nullable=true)
     */
    protected $synchronisationTime;

    /**
     * @var string|null
     * @JMS\Type("string")
     * @JMS\Groups({"synchronisation"})
     * @ORM\Column(name="synchronisation_error", type="text", length=65535, options={"default": null}, nullable=true)
     */
    protected $synchronisationError;

    /**
     * @var User|null
     * @JMS\Type("App\Entity\User")
     * @JMS\Groups({"synchronisation"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="synchronised_by", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $synchronisedBy;

    public function getSynchronisationStatus(): ?string
    {
        return $this->synchronisationStatus;
    }

    /**
     * @param string $status
     *
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
     * @param DateTime $time
     *
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
     * @param string $error
     *
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
     * @param User $user
     *
     * @return $this
     */
    public function setSynchronisedBy(?User $user)
    {
        $this->synchronisedBy = $user;

        return $this;
    }
}
