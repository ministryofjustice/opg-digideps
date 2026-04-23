<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use OPG\Digideps\Backend\Entity\SynchronisableTrait;
use OPG\Digideps\Backend\Entity\Traits\CreationAudit;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Repository\DocumentRepository;
use OPG\Digideps\Backend\Entity\SynchronisableInterface;

/**
 * Documents
 */
#[ORM\Table(name: 'document')]
#[ORM\Index(columns: ['report_id'], name: 'ix_document_report_id')]
#[ORM\Index(columns: ['created_by'], name: 'ix_document_created_by')]
#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Index(columns: ['report_id'], name: 'ix_document_report_id')]
#[ORM\Index(columns: ['created_by'], name: 'ix_document_created_by')]
class Document implements SynchronisableInterface
{
    use CreationAudit;
    use SynchronisableTrait;

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['documents', 'document-id'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'user_id_seq', allocationSize: 1, initialValue: 1)]
    private $id;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['documents'])]
    #[ORM\Column(name: 'filename', type: 'string', length: 255, nullable: false)]
    private $fileName;

    /**
     * Set to null when documents belong to a reportSubmission and documentsAvailable is set to false.
     *
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['document-storage-reference', 'documents'])]
    #[ORM\Column(name: 'storage_reference', type: 'string', length: 512, nullable: true)]
    private $storageReference;

    /**
     * @var bool
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['documents'])]
    #[ORM\Column(name: 'is_report_pdf', type: 'boolean', nullable: false, options: ['default' => false])]
    private $isReportPdf;

    /**
     * @var Report
     */
    #[JMS\Groups(['document-report'])]
    #[JMS\Type(Report::class)]
    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'documents')]
    private $report;

    /**
     * @var ReportSubmission
     */
    #[JMS\Type(ReportSubmission::class)]
    #[JMS\Groups(['document-report-submission'])]
    #[ORM\JoinColumn(name: 'report_submission_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ReportSubmission::class, cascade: ['persist'], inversedBy: 'documents')]
    private $reportSubmission;

    /**
     * @var int|null
     *
     *
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['synchronisation'])]
    #[ORM\Column(name: 'sync_attempts', type: 'integer', nullable: false, options: ['default' => 0])]
    protected $syncAttempts = 0;

    /**
     * Document constructor.
     *
     * Report is initially required, but will be set to null at submission time,
     * and associated to a specific ReportSubmission instead
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
        $this->isReportPdf = true;
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
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     *
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getStorageReference()
    {
        return $this->storageReference;
    }

    /**
     * @param string $storageReference
     *
     * @return $this
     */
    public function setStorageReference($storageReference)
    {
        $this->storageReference = $storageReference;

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
     * @return $this
     */
    public function setReport(Report $report)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return ReportSubmission|null
     */
    public function getReportSubmission()
    {
        return $this->reportSubmission;
    }

    /**
     * @return Document
     */
    public function setReportSubmission(ReportSubmission $reportSubmission)
    {
        $this->reportSubmission = $reportSubmission;
        $reportSubmission->addDocument($this);

        return $this;
    }

    /**
     * @return bool
     */
    public function isReportPdf()
    {
        return $this->isReportPdf;
    }

    /**
     * @param bool $isReportPdf
     *
     * @return Document
     */
    public function setIsReportPdf($isReportPdf)
    {
        $this->isReportPdf = $isReportPdf;

        return $this;
    }

    /**
     * Is document for OPG admin eyes only.
     *
     * @return bool
     */
    public function isAdminDocument()
    {
        return $this->isReportPdf() || $this->isTransactionDocument();
    }

    /**
     * Is document a list of transaction document (admin only).
     *
     * @return bool|int
     */
    private function isTransactionDocument()
    {
        return false !== strpos($this->getFileName(), 'DigiRepTransactions');
    }

    public function getSyncAttempts(): ?int
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
}
