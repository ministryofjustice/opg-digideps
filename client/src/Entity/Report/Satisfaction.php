<?php

declare(strict_types=1);

namespace App\Entity\Report;

use App\Entity\Ndr\Ndr;
use DateTime;
use JMS\Serializer\Annotation as JMS;

class Satisfaction
{
    /**
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @JMS\Type("integer")
     */
    private $score;

    /**
     * @JMS\Type("string")
     */
    private $comments;

    /**
     * @JMS\Type("string")
     */
    private $deputyrole;

    /**
     * @JMS\Type("string")
     */
    private $reporttype;

    /**
     * @JMS\Type("DateTime")
     */
    private $created;

    /**
     * @JMS\Type("App\Entity\UserResearch\UserResearchResponse")
     */
    private UserResearchResponse $userResearchResponse;

    /**
     * @JMS\Type("App\Entity\Report\Report")
     */
    private ?Report $report = null;

    /**
     * @JMS\Type("App\Entity\Ndr\Ndr")
     */
    private ?Ndr $ndr = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Satisfaction
    {
        $this->id = $id;

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): Satisfaction
    {
        $this->score = $score;

        return $this;
    }

    public function getComments(): string
    {
        return $this->comments;
    }

    public function setComments(string $comments): Satisfaction
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyrole()
    {
        return $this->deputyrole;
    }

    /**
     * @param string $deputyrole
     */
    public function setDeputyrole($deputyrole): Satisfaction
    {
        $this->deputyrole = $deputyrole;

        return $this;
    }

    /**
     * @return string
     */
    public function getReporttype()
    {
        return $this->reporttype;
    }

    /**
     * @param string $reporttype
     */
    public function setReporttype($reporttype): Satisfaction
    {
        $this->reporttype = $reporttype;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated($created): Satisfaction
    {
        $this->created = $created;

        return $this;
    }

    public function getUserResearchResponse(): UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    public function setUserResearchResponse(UserResearchResponse $userResearchResponse): Satisfaction
    {
        $this->userResearchResponse = $userResearchResponse;

        return $this;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): Satisfaction
    {
        $this->report = $report;

        return $this;
    }

    public function getNdr(): ?Ndr
    {
        return $this->ndr;
    }

    public function setNdr(?Ndr $ndr): Satisfaction
    {
        $this->ndr = $ndr;

        return $this;
    }
}
