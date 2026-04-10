<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\UserResearch\UserResearchResponse;
use JMS\Serializer\Annotation as JMS;

class Satisfaction
{
    #[JMS\Type('integer')]
    private int $id;

    #[JMS\Type('integer')]
    private int $score;

    #[JMS\Type('string')]
    private string $comments;

    #[JMS\Type('string')]
    private string $deputyrole;

    #[JMS\Type('string')]
    private string $reporttype;

    #[JMS\Type('\DateTime')]
    private \DateTime $created;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\UserResearch\UserResearchResponse')]
    private UserResearchResponse $userResearchResponse;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Report')]
    private ?Report $report = null;

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

    public function getDeputyrole(): string
    {
        return $this->deputyrole;
    }

    public function setDeputyrole(string $deputyrole): Satisfaction
    {
        $this->deputyrole = $deputyrole;

        return $this;
    }

    public function getReporttype(): string
    {
        return $this->reporttype;
    }

    public function setReporttype(string $reporttype): Satisfaction
    {
        $this->reporttype = $reporttype;

        return $this;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): static
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
}
