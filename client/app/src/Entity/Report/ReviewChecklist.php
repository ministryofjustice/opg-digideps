<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use OPG\Digideps\Frontend\Entity\Traits\ModifyAudit;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Model\FullReviewChecklist;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ReviewChecklist
{
    use HasReportTrait;
    use ModifyAudit;

    #[JMS\Type('integer')]
    private int $id;

    #[Assert\Valid]
    #[JMS\Type('OPG\Digideps\Frontend\Model\FullReviewChecklist')]
    private ?FullReviewChecklist $answers = null;

    #[JMS\Type('string')]
    private ?string $decision = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\User')]
    protected ?User $submittedBy = null;

    #[JMS\Type('DateTime')]
    protected ?\DateTime $submittedOn = null;

    #[JMS\Type('boolean')]
    protected bool $isSubmitted = false;

    public function __construct(Report $report)
    {
        $this->setReport($report);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getAnswers(): ?FullReviewChecklist
    {
        return $this->answers ?: new FullReviewChecklist();
    }

    public function setAnswers(FullReviewChecklist $answers): static
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

    public function setSubmittedBy(User $submittedBy): static
    {
        $this->submittedBy = $submittedBy;
        $this->isSubmitted = true;

        return $this;
    }

    public function getSubmittedOn(): ?\DateTime
    {
        return $this->submittedOn;
    }

    public function setSubmittedOn(\DateTime $submittedOn): static
    {
        $this->submittedOn = $submittedOn;
        $this->isSubmitted = true;

        return $this;
    }

    public function getIsSubmitted(): bool
    {
        return $this->isSubmitted;
    }

    public function setIsSubmitted(bool $isSubmitted): static
    {
        $this->isSubmitted = $isSubmitted;

        return $this;
    }
}
