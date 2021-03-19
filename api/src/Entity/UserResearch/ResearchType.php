<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

use Doctrine\ORM\Mapping as ORM;

use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

/**
 * @ORM\Entity()
 * @ORM\Table(name="research_type")
 */
class ResearchType
{
    public function __construct(array $formResponses, ?UuidInterface $id = null)
    {
        if (empty($formResponses)) {
            throw new RuntimeException('Must select at least one research type', 403);
        }

        $this->id = $id ?? Uuid::uuid4();

        $setters = array_map(function ($response) {
            return sprintf('set%s', ucfirst($response));
        }, $formResponses);

        foreach ($setters as $setter) {
            $this->$setter(true);
        }
    }

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserResearch\UserResearchResponse", mappedBy="researchType", cascade={"persist", "remove"})
     */
    private UserResearchResponse $userResearchResponse;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="uuid")
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(name="surveys", type="boolean", nullable=true)
     */
    private ?bool $surveys = null;

    /**
     * @ORM\Column(name="video_call", type="boolean", nullable=true)
     */
    private ?bool $videoCall = null;

    /**
     * @ORM\Column(name="phone", type="boolean", nullable=true)
     */
    private ?bool $phone = null;

    /**
     * @ORM\Column(name="in_person", type="boolean", nullable=true)
     */
    private ?bool $inPerson = null;

    /**
     * @return UserResearchResponse
     */
    public function getUserResearchResponse(): UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    /**
     * @param UserResearchResponse $userResearchResponse
     * @return ResearchType
     */
    public function setUserResearchResponse(UserResearchResponse $userResearchResponse): ResearchType
    {
        $this->userResearchResponse = $userResearchResponse;
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
     */
    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool|null
     */
    public function getSurveys(): ?bool
    {
        return $this->surveys;
    }

    /**
     * @param bool|null $surveys
     * @return ResearchType
     */
    public function setSurveys(?bool $surveys): ResearchType
    {
        $this->surveys = $surveys;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getVideoCall(): ?bool
    {
        return $this->videoCall;
    }

    /**
     * @param bool|null $videoCall
     * @return ResearchType
     */
    public function setVideoCall(?bool $videoCall): ResearchType
    {
        $this->videoCall = $videoCall;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getPhone(): ?bool
    {
        return $this->phone;
    }

    /**
     * @param bool|null $phone
     * @return ResearchType
     */
    public function setPhone(?bool $phone): ResearchType
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getInPerson(): ?bool
    {
        return $this->inPerson;
    }

    /**
     * @param bool|null $inPerson
     * @return ResearchType
     */
    public function setInPerson(?bool $inPerson): ResearchType
    {
        $this->inPerson = $inPerson;
        return $this;
    }
}
