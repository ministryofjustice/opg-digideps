<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class Satisfaction
{
    #[JMS\Type('integer')]
    private ?int $id = null;

    #[JMS\Type('integer')]
    private int $score;

    #[JMS\Type('DateTime')]
    private \DateTime $created;

    #[JMS\Type('string')]
    private ?string $comments = null;

    #[JMS\Type('string')]
    private ?string $deputyRole = null;

    #[JMS\Type('string')]
    private ?string $reportType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

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

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): static
    {
        $this->comments = $comments;

        return $this;
    }

    public function getDeputyRole(): ?string
    {
        return $this->deputyRole;
    }

    public function setDeputyRole(?string $deputyRole): static
    {
        $this->deputyRole = $deputyRole;

        return $this;
    }

    public function getReportType(): ?string
    {
        return $this->reportType;
    }

    public function setReportType(?string $reportType): static
    {
        $this->reportType = $reportType;

        return $this;
    }
}
