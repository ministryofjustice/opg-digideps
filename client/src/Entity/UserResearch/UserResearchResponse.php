<?php declare(strict_types=1);


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

    /**
     * @return string
     */
    public function getDeputyshipLength(): string
    {
        return $this->deputyshipLength;
    }

    /**
     * @param string $deputyshipLength
     * @return UserResearchResponse
     */
    public function setDeputyshipLength(string $deputyshipLength): UserResearchResponse
    {
        $this->deputyshipLength = $deputyshipLength;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasAccessToVideoCallDevice(): bool
    {
        return $this->hasAccessToVideoCallDevice;
    }

    /**
     * @param bool $hasAccessToVideoCallDevice
     * @return UserResearchResponse
     */
    public function setHasAccessToVideoCallDevice(bool $hasAccessToVideoCallDevice): UserResearchResponse
    {
        $this->hasAccessToVideoCallDevice = $hasAccessToVideoCallDevice;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UserResearchResponse
     */
    public function setId(int $id): UserResearchResponse
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return UserResearchResponse
     */
    public function setUser(?User $user): UserResearchResponse
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreated(): ?DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime|null $created
     * @return UserResearchResponse
     */
    public function setCreated(?DateTime $created): UserResearchResponse
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return ResearchType
     */
    public function getResearchType(): ResearchType
    {
        return $this->researchType;
    }

    /**
     * @param ResearchType $researchType
     * @return UserResearchResponse
     */
    public function setResearchType(ResearchType $researchType): UserResearchResponse
    {
        $this->researchType = $researchType;
        return $this;
    }

    /**
     * @return Satisfaction
     */
    public function getSatisfaction(): Satisfaction
    {
        return $this->satisfaction;
    }

    /**
     * @param Satisfaction $satisfaction
     * @return UserResearchResponse
     */
    public function setSatisfaction(Satisfaction $satisfaction): UserResearchResponse
    {
        $this->satisfaction = $satisfaction;
        return $this;
    }
}
