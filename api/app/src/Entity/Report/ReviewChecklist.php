<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\ModifyAudit;
use OPG\Digideps\Backend\Entity\User;

/**
 * Checklist.
 *
 *
 */
#[ORM\Table(name: 'review_checklist')]
#[ORM\Entity]
class ReviewChecklist
{
    use ModifyAudit;

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['checklist'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'checklist_id_seq', allocationSize: 1, initialValue: 1)]
    private $id;


    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Report')]
    #[JMS\Groups(['checklist'])]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'checklist', targetEntity: Report::class)]
    private $report;

    /**
     * @var array
     */
    #[JMS\Groups(['checklist'])]
    #[ORM\Column(name: 'answers', type: 'json', nullable: true)]
    private $answers;

    /**
     * @var string
     *
     */
    #[JMS\Groups(['checklist'])]
    #[ORM\Column(name: 'decision', type: 'string', length: 30, nullable: true)]
    private $decision;

    /**
     * @var User
     */
    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[JMS\Groups(['checklist'])]
    #[ORM\JoinColumn(name: 'submitted_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    protected $submittedBy;

    /**
     * @var \DateTime
     */
    #[JMS\Type('DateTime')]
    #[JMS\Groups(['checklist'])]
    #[ORM\Column(name: 'submitted_on', type: 'datetime', nullable: true)]
    protected $submittedOn;

    public function __construct(Report $report)
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

    public function getReport(): Report
    {
        return $this->report;
    }

    /**
     * @return $this
     */
    public function setReport(Report $report)
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
     * @return User
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

        return $this;
    }
}
