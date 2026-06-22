<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\UserResearch\UserResearchResponse;
use OPG\Digideps\Backend\Repository\SatisfactionRepository;

#[ORM\Table(name: 'satisfaction')]
#[ORM\Entity(repositoryClass: SatisfactionRepository::class)]
class Satisfaction
{
    #[JMS\Type('integer')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'satisfaction_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Type('integer')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(type: 'integer')]
    private int $score;

    #[JMS\Type('string')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'comments', type: 'string', length: 1200, nullable: true)]
    private ?string $comments;

    #[JMS\Type('string')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'deputy_role', type: 'string', length: 50, nullable: true)]
    private ?string $deputyRole = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'report_type', type: 'string', length: 9, nullable: true)]
    private ?string $reportType = null;


    #[JMS\Type('DateTime')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTime $created;

    #[JMS\Type('OPG\Digideps\Backend\Entity\UserResearch\UserResearchResponse')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\OneToOne(mappedBy: 'satisfaction', targetEntity: UserResearchResponse::class, cascade: ['persist', 'remove'])]
    private ?UserResearchResponse $userResearchResponse = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Report')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\OneToOne(inversedBy: 'satisfaction', targetEntity: Report::class, cascade: ['persist'])]
    private ?Report $report = null;

    public function __construct(int $score, ?string $comments = null)
    {
         $this->score = $score;
         $this->comments = $comments;
         $this->created = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

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

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): Satisfaction
    {
        $this->comments = $comments;

        return $this;
    }

    public function getDeputyRole(): ?string
    {
        return $this->deputyRole;
    }

    public function setDeputyRole(string $deputyRole): static
    {
        $this->deputyRole = $deputyRole;

        return $this;
    }

    public function getReportType(): ?string
    {
        return $this->reportType;
    }

    public function setReportType(string $reportType): static
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

    public function getUserResearchResponse(): ?UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    public function setUserResearchResponse(?UserResearchResponse $userResearchResponse): static
    {
        $this->userResearchResponse = $userResearchResponse;

        return $this;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): static
    {
        $this->report = $report;

        return $this;
    }
}
