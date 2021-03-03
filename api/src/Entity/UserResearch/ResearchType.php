<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="research_type")
 */
class ResearchType
{
    public function __construct(array $formResponses)
    {
        $this->id = $id ?? Uuid::uuid4();

        $setters = array_map(function ($response) {
            return sprintf('set%s', ucfirst($response));
        }, $formResponses);

        foreach ($setters as $setter) {
            $this->$setter(true);
        }
    }

    /**
     * @ORM\OneToOne (targetEntity="UserResearchResponse")
     * @ORM\Column(name="user_research_response_id", type="integer", nullable=false)
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
     * @ORM\Column(name="surveys", type="boolean")
     */
    private bool $surveys;

    /**
     * @ORM\Column(name="video_call", type="boolean")
     */
    private bool $videoCall;

    /**
     * @ORM\Column(name="phone", type="boolean")
     */
    private bool $phone;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $inPerson;

    /**
     * @return bool
     */
    public function getSurveys(): bool
    {
        return $this->surveys;
    }

    /**
     * @param bool $surveys
     * @return ResearchType
     */
    public function setSurveys(bool $surveys): ResearchType
    {
        $this->surveys = $surveys;
        return $this;
    }

    /**
     * @return bool
     */
    public function getVideoCall(): bool
    {
        return $this->videoCall;
    }

    /**
     * @param bool $videoCall
     * @return ResearchType
     */
    public function setVideoCall(bool $videoCall): ResearchType
    {
        $this->videoCall = $videoCall;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPhone(): bool
    {
        return $this->phone;
    }

    /**
     * @param bool $phone
     * @return ResearchType
     */
    public function setPhone(bool $phone): ResearchType
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return bool
     */
    public function getInPerson(): bool
    {
        return $this->inPerson;
    }

    /**
     * @param bool $inPerson
     * @return ResearchType
     */
    public function setInPerson(bool $inPerson): ResearchType
    {
        $this->inPerson = $inPerson;
        return $this;
    }

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
}
