<?php declare(strict_types=1);


namespace App\Entity\UserResearch;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="research_type")
 */
class ResearchType
{
    /**
     * @ORM\OneToOne (targetEntity="UserResearchSubmission")
     * @ORM\Column(name="user_research_submission_id", type="integer", nullable=false)
     */
    private UserResearchSubmission $userResearchSubmission;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $surveys;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $videoCall;

    /**
     * @ORM\Column(type="boolean")
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
     * @return UserResearchSubmission
     */
    public function getUserResearchSubmission(): UserResearchSubmission
    {
        return $this->userResearchSubmission;
    }

    /**
     * @param UserResearchSubmission $userResearchSubmission
     * @return ResearchType
     */
    public function setUserResearchSubmission(UserResearchSubmission $userResearchSubmission): ResearchType
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
