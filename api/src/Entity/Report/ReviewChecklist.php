<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\Traits\ModifyAudit;
use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Checklist.
 *
 * @ORM\Table(name="review_checklist")
 * @ORM\Entity()
 */
class ReviewChecklist
{
    use ModifyAudit;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"checklist"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="checklist_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Type("AppBundle\Entity\Report\Report")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="checklist")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @JMS\Groups({"checklist"})
     */
    private $report;

    /**
     * @var array
     * @JMS\Groups({"checklist"})
     * @ORM\Column(name="answers", type="json", nullable=true)
     */
    private $answers;

    /**
     * @var string
     *
     * @JMS\Groups({"checklist"})
     *
     * @ORM\Column(name="decision", type="string", length=30, nullable=true)
     */
    private $decision;

    /**
     * @var \AppBundle\Entity\User
     *
     * @JMS\Type("AppBundle\Entity\User")
     * @JMS\Groups({"checklist"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="submitted_by", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $submittedBy;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime")
     * @JMS\Groups({"checklist"})
     * @ORM\Column(type="datetime", name="submitted_on", nullable=true)
     */
    protected $submittedOn;

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
     * @return string
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * @param array $answers
     *
     * @return $this
     */
    public function setAnswers(array $answers)
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
     * @param string|null $decision
     *
     * @return $this
     */
    public function setDecision($decision = null)
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
