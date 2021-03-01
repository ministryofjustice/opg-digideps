<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

class ResearchType
{
    private UserResearchResponse $userResearchSubmission;
    private int $id;
    private bool $surveys;
    private bool $videoCall;
    private bool $phone;
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
    public function getUserResearchSubmission(): UserResearchResponse
    {
        return $this->userResearchSubmission;
    }

    /**
     * @param UserResearchResponse $userResearchSubmission
     * @return ResearchType
     */
    public function setUserResearchSubmission(UserResearchResponse $userResearchSubmission): ResearchType
    {
        $this->userResearchSubmission = $userResearchSubmission;
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
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
