<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

class ResearchType
{
    /**
     * @JMS\Type("App\Entity\UserResearch\UserResearchResponse")
     */
    private $userResearchResponse;

    /**
     * @JMS\Type("string")
     */
    private $id;

    /**
     * @JMS\Type("bool")
     */
    private ?bool $surveys = null;

    /**
     * @JMS\Type("bool")
     */
    private ?bool $videoCall = null;

    /**
     * @JMS\Type("bool")
     */
    private ?bool $phone = null;

    /**
     * @JMS\Type("bool")
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

    public function getCommaSeparatedTypesAgreed()
    {
        $props = get_object_vars($this);

        $types = [];

        foreach ($props as $propName => $value) {
            if ($value) {
                $types[] = $propName;
            }
        }

        return implode(',', $types);
    }
}
