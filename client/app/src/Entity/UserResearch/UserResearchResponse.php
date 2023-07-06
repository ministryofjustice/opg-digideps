<?php

declare(strict_types=1);

namespace App\Entity\UserResearch;

use App\Entity\Report\Satisfaction;
use App\Entity\User;
use DateTime;
use JMS\Serializer\Annotation as JMS;

class UserResearchResponse
{
    const UNDER_ONE = 'underOne';
    const ONE_TO_FIVE = 'oneToFive';
    const SIX_TO_TEN = 'sixToTen';
    const OVER_TEN = 'overTen';

    /**
     * @JMS\Type("App\Entity\UserResearch\ResearchType")
     */
    private $researchType;

    /**
     * @JMS\Type("App\Entity\User")
     */
    private User $user;

    /**
     * @JMS\Type("int")
     */
    private int $id;

    /**
     * @JMS\Type("string")
     */
    private string $deputyshipLength;

    /**
     * @JMS\Type("boolean")
     */
    private bool $hasAccessToVideoCallDevice;

    /**
     * @JMS\Type("DateTime")
     */
    private $created;

    /**
     * @JMS\Type("App\Entity\Report\Satisfaction")
     */
    private $satisfaction;

    public function getDeputyshipLength(): string
    {
        return $this->deputyshipLength;
    }

    public function setDeputyshipLength(string $deputyshipLength): UserResearchResponse
    {
        $this->deputyshipLength = $deputyshipLength;

        return $this;
    }

    public function getHasAccessToVideoCallDevice(): bool
    {
        return $this->hasAccessToVideoCallDevice;
    }

    public function setHasAccessToVideoCallDevice(bool $hasAccessToVideoCallDevice): UserResearchResponse
    {
        $this->hasAccessToVideoCallDevice = $hasAccessToVideoCallDevice;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): UserResearchResponse
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): UserResearchResponse
    {
        $this->user = $user;

        return $this;
    }

    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    public function setCreated(?DateTime $created): UserResearchResponse
    {
        $this->created = $created;

        return $this;
    }

    public function getResearchType(): ResearchType
    {
        return $this->researchType;
    }

    public function setResearchType(ResearchType $researchType): UserResearchResponse
    {
        $this->researchType = $researchType;

        return $this;
    }

    public function getSatisfaction(): Satisfaction
    {
        return $this->satisfaction;
    }

    public function setSatisfaction(Satisfaction $satisfaction): UserResearchResponse
    {
        $this->satisfaction = $satisfaction;

        return $this;
    }
}
