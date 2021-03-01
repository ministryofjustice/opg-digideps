<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="user_research_submission")
 */
class UserResearchSubmission
{
    /**
     * @ORM\OneToOne(targetEntity="ResearchType")
     * @ORM\Column(name="research_type_id", type="integer", nullable=false)
     */
    private ResearchType $agreedResearchTypes;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $deputyshipLength;

    /**
     * @ORM\Column(type="boolean")
     */
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
     * @return UserResearchSubmission
     */
    public function setDeputyshipLength(string $deputyshipLength): UserResearchSubmission
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
     * @return UserResearchSubmission
     */
    public function setAgreedResearchTypes(ResearchType $agreedResearchTypes): UserResearchSubmission
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
     * @return UserResearchSubmission
     */
    public function setHasAccessToVideoCallDevice(bool $hasAccessToVideoCallDevice): UserResearchSubmission
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
     * @return UserResearchSubmission
     */
    public function setId(int $id): UserResearchSubmission
    {
        $this->id = $id;
        return $this;
    }
}
