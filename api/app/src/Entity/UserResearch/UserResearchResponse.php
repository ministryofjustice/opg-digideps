<?php

declare(strict_types=1);

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
 *
 * @ORM\Table(name="user_research_response")
 */
class UserResearchResponse
{
    public function __construct(UuidInterface $id = null)
    {
        $this->id = $id ?? Uuid::uuid4();
        $this->created = new \DateTime();
    }

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserResearch\ResearchType", inversedBy="userResearchResponse", cascade={"persist", "remove"})
     *
     * @JMS\Type("App\Entity\UserResearch\ResearchType")
     *
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private ResearchType $researchType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userResearchResponses", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Type("App\Entity\User")
     *
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private User $user;

    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id", type="uuid")
     *
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(name="deputyship_length", type="string")
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private string $deputyshipLength;

    /**
     * @ORM\Column(name="has_access_to_video_call_device", type="boolean")
     *
     * @JMS\Type("boolean")
     *
     * @JMS\Groups({"user-research", "satisfaction"})
     */
    private bool $hasAccessToVideoCallDevice;

    /**
     * @JMS\Type("DateTime")
     *
     * @JMS\Groups({"user-research", "satisfaction"})
     *
     * @ORM\Column(name="created_at", type="datetime",nullable=true)
     *
     * @Gedmo\Timestampable(on="create")
     */
    private \DateTime $created;

    /**
     * @JMS\Type("App\Entity\Satisfaction")
     *
     * @JMS\Groups({"user-research", "satisfaction"})
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Satisfaction", inversedBy="userResearchResponse", cascade={"persist", "remove"})
     */
    private Satisfaction $satisfaction;

    public function getDeputyshipLength(): string
    {
        return $this->deputyshipLength;
    }

    public function setDeputyshipLength(string $deputyshipLength): UserResearchResponse
    {
        $this->deputyshipLength = $deputyshipLength;

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

    public function getHasAccessToVideoCallDevice(): bool
    {
        return $this->hasAccessToVideoCallDevice;
    }

    public function setHasAccessToVideoCallDevice(bool $hasAccessToVideoCallDevice): UserResearchResponse
    {
        $this->hasAccessToVideoCallDevice = $hasAccessToVideoCallDevice;

        return $this;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): UserResearchResponse
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): UserResearchResponse
    {
        $this->user = $user;

        return $this;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): UserResearchResponse
    {
        $this->created = $created;

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
