<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Repository\UserResearchResponseRepository")
 * @ORM\Table(name="user_research_response")
 */
class UserResearchResponse
{
    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id ?? Uuid::uuid4();
    }

    /**
     * @ORM\OneToOne(targetEntity="ResearchType")
     * @ORM\Column(name="research_type_id", type="integer", nullable=false)
     */
    private ResearchType $agreedResearchTypes;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="uuid")
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(name="deputyship_length", type="string")
     */
    private string $deputyshipLength;

    /**
     * @ORM\Column(name="has_access_to_video_call_device", type="boolean")
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
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @param UuidInterface $id
     * @return UserResearchResponse
     */
    public function setId(UuidInterface $id): UserResearchResponse
    {
        $this->id = $id;
        return $this;
    }
}
