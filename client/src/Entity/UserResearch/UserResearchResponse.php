<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

use App\Entity\User;
use DateTime;

class UserResearchResponse
{
    private array $agreedResearchTypes;
    private ?int $id = null;
    private string $deputyshipLength = '';
    private bool $hasAccessToVideoCallDevice = false;
    private ?DateTime $created = null;

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
     * @return array
     */
    public function getAgreedResearchTypes(): array
    {
        return $this->agreedResearchTypes;
    }

    /**
     * @param array $agreedResearchTypes
     * @return UserResearchResponse
     */
    public function setAgreedResearchTypes(array $agreedResearchTypes): UserResearchResponse
    {
        $this->agreedResearchTypes = $agreedResearchTypes;
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
}
