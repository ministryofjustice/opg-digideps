<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

use App\Entity\User;
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
     * @ORM\OneToOne(targetEntity="App\Entity\UserResearch\ResearchType", inversedBy="userResearchResponse", cascade={"persist", "remove"})
     */
    private ResearchType $researchType;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="userResearchResponse", cascade={"persist"})
     */
    private User $user;

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

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserResearchResponse
     */
    public function setUser(User $user): UserResearchResponse
    {
        $this->user = $user;
        return $this;
    }
}
