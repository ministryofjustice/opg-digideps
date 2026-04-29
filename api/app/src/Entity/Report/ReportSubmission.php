<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\CreationAudit;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\ReportSubmissionRepository;

#[ORM\Table(name: 'report_submission')]
#[ORM\Index(columns: ['created_on'], name: 'rs_created_on_idx')]
#[ORM\Entity(repositoryClass: ReportSubmissionRepository::class)]
class ReportSubmission
{
    // createdBy is the user who submitted the report
    // createdOn = date where the report (or documents-only) get submitted
    use CreationAudit;

    public const string REMOVE_FILES_WHEN_OLDER_THAN = '-500 days';

    #[JMS\Type('integer')]
    #[JMS\Groups(['report-submission', 'report-submission-id'])]
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'report_submission_id_seq', allocationSize: 1, initialValue: 1)]
    private int $id;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Report')]
    #[JMS\Groups(['report-submission'])]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, cascade: ['persist'], inversedBy: 'reportSubmissions')]
    private ?Report $report;

    /**
     * @var Collection<int, Document>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Document>')]
    #[JMS\Groups(['report-submission', 'report-submission-documents'])]
    #[ORM\JoinColumn(name: 'report_submission_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OneToMany(mappedBy: 'reportSubmission', targetEntity: Document::class, cascade: ['persist'])]
    #[ORM\OrderBy(['createdBy' => 'ASC'])]
    private Collection $documents;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['report-submission'])]
    #[ORM\Column(name: 'archived', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $archived = false;

    #[JMS\Type('OPG\Digideps\Backend\Entity\User')]
    #[JMS\Groups(['report-submission'])]
    #[ORM\JoinColumn(name: 'archived_by', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    private ?User $archivedBy;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['report-submission'])]
    #[ORM\Column(name: 'downloadable', type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $downloadable;

    #[JMS\Type('string')]
    #[JMS\Groups(['report-submission', 'report-submission-uuid'])]
    #[ORM\Column(name: 'opg_uuid', type: 'string', length: 36, nullable: true)]
    private ?string $uuid;

    public function __construct(Report $report, ?User $createdBy)
    {
        $this->report = $report;
        $this->report->addReportSubmission($this); // double-link for UNIT test purposes
        $this->documents = new ArrayCollection();
        $this->createdBy = $createdBy;
        $this->downloadable = true;
        $this->uuid = null;
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
