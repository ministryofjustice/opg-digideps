<?php

namespace App\Entity\Report;

use App\Entity\Traits\CreationAudit;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="report_submission",
 *     indexes={
 *
 *     @ORM\Index(name="rs_created_on_idx", columns={"created_on"})
 *  })
 *
 * @ORM\Entity(repositoryClass="App\Repository\ReportSubmissionRepository")
 */
class ReportSubmission
{
    // createdBy is the user who submitted the report
    // createdOn = date where the report (or documents-only) get submitted
    use CreationAudit;

    public const string REMOVE_FILES_WHEN_OLDER_THAN = '-500 days';

    /**
     * @JMS\Type("integer")
     *
     * @JMS\Groups({"report-submission", "report-submission-id"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="report_submission_id_seq", allocationSize=1, initialValue=1)
     */
    private int $id;

    /**
     * @JMS\Type("App\Entity\Report\Report")
     *
     * @JMS\Groups({"report-submission"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="reportSubmissions", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Report $report;

    /**
     * @var Collection<int, Document>
     *
     * @JMS\Type("ArrayCollection<App\Entity\Report\Document>")
     *
     * @JMS\Groups({"report-submission", "report-submission-documents"})
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\Document", mappedBy="reportSubmission", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="report_submission_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @ORM\OrderBy({"createdBy"="ASC"})
     */
    private Collection $documents;

    /**
     * @JMS\Type("boolean")
     *
     * @JMS\Groups({"report-submission"})
     *
     * @ORM\Column(name="archived", type="boolean", options={"default": false}, nullable=false)
     */
    private bool $archived = false;

    /**
     * @JMS\Type("App\Entity\User")
     *
     * @JMS\Groups({"report-submission"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="archived_by", referencedColumnName="id", onDelete="SET NULL")
     */
    private ?User $archivedBy;

    /**
     * @JMS\Type("boolean")
     *
     * @JMS\Groups({"report-submission"})
     *
     * @ORM\Column(name="downloadable", type="boolean", options={ "default": true}, nullable=false)
     */
    private bool $downloadable;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"report-submission", "report-submission-uuid"})
     *
     * @ORM\Column(name="opg_uuid", type="string", length=36, nullable=true)
     */
    private ?string $uuid;

    public function __construct(Report $report, User $createdBy)
    {
        $this->report = $report;
        $this->report->addReportSubmission($this); // double-link for UNIT test purposes
        $this->documents = new ArrayCollection();
        $this->createdBy = $createdBy;
        $this->downloadable = true;
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

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }

        return $this;
    }

    public function getArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): static
    {
        $this->archived = $archived;

        return $this;
    }

    public function getArchivedBy(): ?User
    {
        return $this->archivedBy;
    }

    public function setArchivedBy(?User $archivedBy = null): static
    {
        $this->archivedBy = $archivedBy;

        return $this;
    }

    public function getDownloadable(): bool
    {
        return $this->downloadable;
    }

    public function setDownloadable(bool $downloadable): static
    {
        $this->downloadable = $downloadable;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }
}
