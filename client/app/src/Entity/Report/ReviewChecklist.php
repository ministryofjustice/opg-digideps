<?php

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasReportTrait;
use App\Entity\ReportInterface;
use App\Entity\Traits\ModifyAudit;
use App\Entity\User;
use App\Model\FullReviewChecklist;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ReviewChecklist
{
    use HasReportTrait;
    use ModifyAudit;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var App\Model\FullReviewChecklist
     *
     * @JMS\Type("App\Model\FullReviewChecklist")\
     * @Assert\Valid
     */
    private $answers;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank(message="checklist.finalDecision.notBlank")
     */
    private $decision;

    /**
     * @var \App\Entity\User
     *
     * @JMS\Type("App\Entity\User")
     */
    protected $submittedBy;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $submittedOn;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $isSubmitted = false;

    /**
     * Checklist constructor.
     */
    public function __construct(ReportInterface $report)
    {
        $this->setReport($report);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getReport(): ReportInterface
    {
        return $this->report;
    }

    /**
     * @return $this
     */
    public function setReport(ReportInterface $report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return FullReviewChecklist
     */
    public function getAnswers(): ?FullReviewChecklist
    {
        return $this->answers ?: new FullReviewChecklist();
    }

    /**
     * @param array $answers
     *
     * @return $this
     */
    public function setAnswers(FullReviewChecklist $answers)
    {
        $this->answers = $answers;

        return $this;
    }

    /**
     * @return string
     */
    public function getDecision(): ?string
    {
        return $this->decision;
    }

    /**
     * @return $this
     */
    public function setDecision(string $decision)
    {
        $this->decision = $decision;

        return $this;
    }

    /**
     * @return \App\Entity\User
     */
    public function getSubmittedBy(): ?User
    {
        return $this->submittedBy;
    }

    /**
     * @return $this
     */
    public function setSubmittedBy(User $submittedBy)
    {
        $this->submittedBy = $submittedBy;
        $this->isSubmitted = true;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSubmittedOn(): ?\DateTime
    {
        return $this->submittedOn;
    }

    /**
     * @return $this
     */
    public function setSubmittedOn(\DateTime $submittedOn)
    {
        $this->submittedOn = $submittedOn;
        $this->isSubmitted = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSubmitted()
    {
        return $this->isSubmitted;
    }

    /**
     * @return $this
     */
    public function setIsSubmitted(bool $isSubmitted)
    {
        $this->isSubmitted = $isSubmitted;

        return $this;
    }
}
