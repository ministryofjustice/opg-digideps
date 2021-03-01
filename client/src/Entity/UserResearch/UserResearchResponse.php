<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

class UserResearchResponse
{
    private ResearchType $agreedResearchTypes;
    private int $id;
    private string $deputyshipLength;
    private bool $hasAccessToVideoCallDevice;

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
     * @return ResearchType
     */
    public function getAgreedResearchTypes(): ResearchType
    {
        return $this->agreedResearchTypes;
    }

    /**
     * @param ResearchType $agreedResearchTypes
     * @return UserResearchResponse
     */
    public function setAgreedResearchTypes(ResearchType $agreedResearchTypes): UserResearchResponse
    {
        $this->agreedResearchTypes = $agreedResearchTypes;
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
}
