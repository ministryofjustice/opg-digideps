<?php

namespace AppBundle\Entity;

use DateTime;

trait SynchronisableTrait
{
    /**
     * @var string|null
     * @JMS\Type("string")
     * @JMS\Groups({"synchronisation"})
     */
    protected $synchronisationStatus;

    /**
     * @var DateTime|null
     * @JMS\Type("DateTime")
     * @JMS\Groups({"synchronisation"})
     */
    protected $synchronisationTime;

    /**
     * @var string|null
     * @JMS\Type("string")
     * @JMS\Groups({"synchronisation"})
     */
    protected $synchronisationError;

    /**
     * @var User|null
     * @JMS\Type("AppBundle\Entity\User")
     * @JMS\Groups({"synchronisation"})
     */
    protected $synchronisedBy;

    /**
     * @return string|null
     */
    public function getSynchronisationStatus(): ?string
    {
        return $this->synchronisationStatus;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setSynchronisationStatus(?string $status)
    {
        if (!in_array($status, array(
            self::SYNC_STATUS_QUEUED,
            self::SYNC_STATUS_IN_PROGRESS,
            self::SYNC_STATUS_SUCCESS,
            self::SYNC_STATUS_TEMPORARY_ERROR,
            self::SYNC_STATUS_PERMANENT_ERROR,
        ))) {
            throw new \InvalidArgumentException('Invalid synchronisation status');
        }

        $this->synchronisationStatus = $status;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getSynchronisationTime(): ?DateTime
    {
        return $this->synchronisationTime;
    }

    /**
     * @param DateTime $time
     * @return $this
     */
    public function setSynchronisationTime(?DateTime $time)
    {
        $this->synchronisationTime = $time;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSynchronisationError(): ?string
    {
        return $this->synchronisationError;
    }

    /**
     * @param string $error
     * @return $this
     */
    public function setSynchronisationError(?string $error)
    {
        $this->synchronisationError = $error;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getSynchronisedBy(): ?User
    {
        return $this->synchronisedBy;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setSynchronisedBy(?User $user)
    {
        $this->synchronisedBy = $user;
        return $this;
    }
}
