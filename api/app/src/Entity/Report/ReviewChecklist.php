<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\ModifyAudit;
use OPG\Digideps\Backend\Entity\User;

#[ORM\Table(name: 'review_checklist')]
#[ORM\Entity]
class ReviewChecklist
{
    use ModifyAudit;

    #[JMS\Type('integer')]
    #[JMS\Groups(['checklist'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'checklist_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Report')]
    #[JMS\Groups(['checklist'])]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'reviewChecklist', targetEntity: Report::class)]
    private Report $report;

    #[JMS\Groups(['checklist'])]
    #[ORM\Column(name: 'answers', type: 'json', nullable: true)]
    private ?array $answers = null;

    #[JMS\Groups(['checklist'])]
    #[ORM\Column(name: 'decision', type: 'string', length: 30, nullable: true)]
    private ?string $decision = null;

    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[JMS\Groups(['checklist'])]
    #[ORM\JoinColumn(name: 'submitted_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    protected ?User $submittedBy = null;

    #[JMS\Type('DateTime')]
    #[JMS\Groups(['checklist'])]
    #[ORM\Column(name: 'submitted_on', type: 'datetime', nullable: true)]
    protected ?\DateTime $submittedOn = null;

    public function __construct(Report $report)
    {
        $this->setReport($report);
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

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getAnswers(): ?array
    {
        return $this->answers;
    }

    public function setAnswers(?array $answers): static
    {
        $this->answers = $answers;

        return $this;
    }

    public function getDecision(): ?string
    {
        return $this->decision;
    }

    public function setDecision(?string $decision): static
    {
        $this->decision = $decision;

        return $this;
    }

    public function getSubmittedBy(): ?User
    {
        return $this->submittedBy;
    }

    public function setSubmittedBy(?User $submittedBy): static
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }

    public function getSubmittedOn(): ?\DateTime
    {
        return $this->submittedOn;
    }

    public function setSubmittedOn(?\DateTime $submittedOn): static
    {
        $this->submittedOn = $submittedOn;

        return $this;
    }
}
