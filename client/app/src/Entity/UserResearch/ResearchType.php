<?php

declare(strict_types=1);

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

    public function getUserResearchResponse(): UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    public function setUserResearchResponse(UserResearchResponse $userResearchResponse): ResearchType
    {
        $this->userResearchResponse = $userResearchResponse;

        return $this;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getSurveys(): ?bool
    {
        return $this->surveys;
    }

    public function setSurveys(?bool $surveys): ResearchType
    {
        $this->surveys = $surveys;

        return $this;
    }

    public function getVideoCall(): ?bool
    {
        return $this->videoCall;
    }

    public function setVideoCall(?bool $videoCall): ResearchType
    {
        $this->videoCall = $videoCall;

        return $this;
    }

    public function getPhone(): ?bool
    {
        return $this->phone;
    }

    public function setPhone(?bool $phone): ResearchType
    {
        $this->phone = $phone;

        return $this;
    }

    public function getInPerson(): ?bool
    {
        return $this->inPerson;
    }

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
