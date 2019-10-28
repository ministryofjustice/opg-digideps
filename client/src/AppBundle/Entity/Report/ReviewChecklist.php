<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\Traits\ModifyAudit;
use AppBundle\Model\FullReviewChecklist;
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
     * @var AppBundle\Model\FullReviewChecklist
     *
     * @JMS\Type("AppBundle\Model\FullReviewChecklist")\
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
     * @var \AppBundle\Entity\User
     *
     * @JMS\Type("AppBundle\Entity\User")
     */
    protected $submittedBy;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $submittedOn;

    /**
     * Checklist constructor.
     *
     * @param ReportInterface $report
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

    /**
     * @return ReportInterface
     */
    public function getReport(): ReportInterface
    {
        return $this->report;
    }

    /**
     * @param ReportInterface $report
     *
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
     * @param string $decision
     *
     * @return $this
     */
    public function setDecision(string $decision)
    {
        $this->decision = $decision;
        return $this;
    }

    /**
     * @return \AppBundle\Entity\User
     */
    public function getSubmittedBy(): ?User
    {
        return $this->submittedBy;
    }

    /**
     * @param \AppBundle\Entity\User $submittedBy
     *
     * @return $this
     */
    public function setSubmittedBy(User $submittedBy)
    {
        $this->submittedBy = $submittedBy;
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
     * @param \DateTime $submittedOn
     *
     * @return $this
     */
    public function setSubmittedOn(\DateTime $submittedOn)
    {
        $this->submittedOn = $submittedOn;
        return $this;
    }
}
