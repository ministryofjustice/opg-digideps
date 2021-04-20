<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

use App\Entity\Satisfaction;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserResearchResponseRepository")
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
     *
     * @JMS\Type("App\Entity\ResearchType")
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private ResearchType $researchType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userResearchResponse", cascade={"persist"})
     *
     * @JMS\Type("App\Entity\User")
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private User $user;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="uuid")
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     * @JMS\Type("string")
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(name="deputyship_length", type="string")
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private string $deputyshipLength;

    /**
     * @ORM\Column(name="has_access_to_video_call_device", type="boolean")
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private bool $hasAccessToVideoCallDevice;

    /**
     * @var DateTime
     * @JMS\Type("DateTime")
     * @JMS\Groups({"user-research", "satisfaction"})
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private DateTime $created;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Satisfaction", mappedBy="satisfaction", cascade={"persist", "remove"})
     */
    private Satisfaction $satisfaction;

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

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     * @return UserResearchResponse
     */
    public function setCreated(DateTime $created): UserResearchResponse
    {
        $this->created = $created;
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
