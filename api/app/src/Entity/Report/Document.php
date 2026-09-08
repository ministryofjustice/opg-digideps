<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use OPG\Digideps\Backend\Entity\SynchronisableTrait;
use OPG\Digideps\Backend\Entity\Traits\CreationAudit;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Repository\DocumentRepository;
use OPG\Digideps\Backend\Entity\SynchronisableInterface;
use OPG\Digideps\Backend\Repository\ReportSubmissionRepository;

#[ORM\Table(name: 'document')]
#[ORM\Index(columns: ['report_id'], name: 'ix_document_report_id')]
#[ORM\Index(columns: ['created_by'], name: 'ix_document_created_by')]
#[ORM\Entity(repositoryClass: DocumentRepository::class), ORM\HasLifecycleCallbacks]
#[ORM\Index(columns: ['report_id'], name: 'ix_document_report_id')]
#[ORM\Index(columns: ['created_by'], name: 'ix_document_created_by')]
class Document implements SynchronisableInterface
{
    use CreationAudit;
    use SynchronisableTrait;

    #[JMS\Type('integer')]
    #[JMS\Groups(['documents', 'document-id'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'user_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['documents'])]
    #[ORM\Column(name: 'filename', type: 'string', length: 255, nullable: false)]
    private string $fileName;

    /**
     * Set to null when documents belong to a reportSubmission and documentsAvailable is set to false.
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['document-storage-reference', 'documents'])]
    #[ORM\Column(name: 'storage_reference', type: 'string', length: 512, nullable: true)]
    private ?string $storageReference = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['documents'])]
    #[ORM\Column(name: 'is_report_pdf', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isReportPdf;

    #[JMS\Groups(['document-report'])]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Report')]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'documents')]
    private Report $report;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\ReportSubmission')]
    #[JMS\Groups(['document-report-submission'])]
    #[ORM\JoinColumn(name: 'report_submission_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ReportSubmission::class, cascade: ['persist'], inversedBy: 'documents')]
    private ?ReportSubmission $reportSubmission = null;

    #[JMS\Type('integer')]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\Column(name: 'sync_attempts', type: 'integer', nullable: false, options: ['default' => 0])]
    protected int $syncAttempts = 0;

    public function __construct(Report $report, string $fileName)
    {
        $this->report = $report;
        $this->fileName = $fileName;
        $this->isReportPdf = true;
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

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getStorageReference(): ?string
    {
        return $this->storageReference;
    }

    public function setStorageReference(?string $storageReference): static
    {
        $this->storageReference = $storageReference;

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

    public function getReportSubmission(): ?ReportSubmission
    {
        return $this->reportSubmission;
    }

    public function setReportSubmission(ReportSubmission $reportSubmission): static
    {
        $this->reportSubmission = $reportSubmission;
        $reportSubmission->addDocument($this);

        return $this;
    }

    public function isReportPdf(): bool
    {
        return $this->isReportPdf;
    }

    public function setIsReportPdf(bool $isReportPdf): static
    {
        $this->isReportPdf = $isReportPdf;

        return $this;
    }

    /**
     * Is document for OPG admin eyes only.
     */
    public function isAdminDocument(): bool
    {
        return $this->isReportPdf() || $this->isTransactionDocument();
    }

    /**
     * Is document a list of transaction document (admin only).
     */
    private function isTransactionDocument(): bool
    {
        return str_contains($this->getFileName(), 'DigiRepTransactions');
    }

    public function getSyncAttempts(): int
    {
        return $this->syncAttempts;
    }

    public function incrementSyncAttempts(): void
    {
        ++$this->syncAttempts;
    }

    public function resetSyncAttempts(): void
    {
        $this->syncAttempts = 0;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(PreUpdateEventArgs $eventArgs): void
    {
        if ($this->getReportSubmission() !== null) {
            /** @var ReportSubmissionRepository $reportSubmissionRepo */
            $reportSubmissionRepo = $eventArgs->getObjectManager()->getRepository(ReportSubmission::class);
            $reportSubmissionRepo->updateArchivedStatus($this->getReportSubmission());
        }
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if ($this->getReport()->getDocuments()->count() === 1) {
            $this->getReport()->setWishToProvideDocumentation(null);
        }
    }
}
