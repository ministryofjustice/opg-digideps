<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\CreationAudit;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="report_submission")
 * @ORM\Entity()
 */
class ReportSubmission
{
    // createdBy is the user who submitted the report
    // createdOn = date where the report (or documents-only) get submitted
    use CreationAudit;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"report-submission"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="report_submission_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Report
     *
     * @JMS\Type("AppBundle\Entity\Report\Report")
     *
     * @JMS\Groups({"report-submission"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="submissions")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("array<AppBundle\Entity\Report\Document>")
     * @JMS\Groups({"report-submission"})
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Document", mappedBy="reportSubmission")
     * @ORM\JoinColumn(name="report_submission_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @ORM\OrderBy({"createdBy"="ASC"})
     */
    private $documents;

    /**
     * @var User
     *
     * @JMS\Type("AppBundle\Entity\User")
     * @JMS\Groups({"report-submission"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="archived_by", referencedColumnName="id", onDelete="SET NULL")
     */
    private $archivedBy;


    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @ORM\Column(name="archived", type="boolean", options={"default": false}, nullable=false)
     */
    private $archived;

    /**
     * ReportSubmission constructor.
     * @param Report $report
     * @param User $createdBy
     */
    public function __construct(Report $report, User $createdBy)
    {
        $this->report = $report;
        $this->report->addSubmissions($this);// double-link for UNIT test purposes
        $this->documents = new ArrayCollection();
        $this->createdBy = $createdBy;
        $this->archived = false;
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
     * @return ReportSubmission
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     * @return ReportSubmission
     */
    public function setReport($report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param Document $document
     * @return $this
     */
    public function addDocument(Document $document)
    {
        if (!$this->documents->contains(($document))) {
            $this->documents->add($document);
        }

        return $this;
    }

    /**
     * @return User
     */
    public function getArchivedBy()
    {
        return $this->archivedBy;
    }

    /**
     * @param User $archivedBy
     * @return ReportSubmission
     */
    public function setArchivedBy($archivedBy)
    {
        $this->archivedBy = $archivedBy;

        return $this;
    }


    /**
     * @return boolean
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param boolean $archived
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
    }

}
