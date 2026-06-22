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
    private string $deputyRole;

    #[JMS\Type('string')]
    private string $reportType;

    #[JMS\Type('DateTime')]
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

    public function getDeputyRole(): string
    {
        return $this->deputyRole;
    }

    public function setDeputyRole(string $deputyRole): Satisfaction
    {
        $this->deputyRole = $deputyRole;

        return $this;
    }

    public function getReportType(): string
    {
        return $this->reportType;
    }

    public function setReportType(string $reportType): Satisfaction
    {
        $this->reportType = $reportType;

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
