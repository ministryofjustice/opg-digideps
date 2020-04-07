<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Traits\CreationAudit;
use AppBundle\Entity\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Documents.
 *
 * @ORM\Table(name="document",
 *     indexes={
 *     @ORM\Index(name="ix_document_report_id", columns={"report_id"}),
 *     @ORM\Index(name="ix_document_created_by", columns={"created_by"})
 *     })
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\DocumentRepository")
 */
class Document
{
    use CreationAudit;

    const SYNC_STATUS_QUEUED = 'QUEUED';
    const SYNC_STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const SYNC_STATUS_SUCCESS = 'SUCCESS';
    const SYNC_STATUS_TEMPORARY_ERROR = 'TEMPORARY_ERROR';
    const SYNC_STATUS_PERMANENT_ERROR = 'PERMANENT_ERROR';

    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"documents", "document-id"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="user_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"documents"})
     *
     * @ORM\Column(name="filename", type="string", length=255, nullable=false)
     */
    private $fileName;

    /**
     * Set to null when documents belong to a reportSubmission and documentsAvailable is set to false
     *
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"document-storage-reference"})
     *
     * @ORM\Column(name="storage_reference", type="string", length=512, nullable=true)
     */
    private $storageReference;


    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"documents"})
     * @ORM\Column(name="is_report_pdf", type="boolean", options={ "default": false}, nullable=false)
     */
    private $isReportPdf;

    /**
     * @var Report
     *
     * @JMS\Groups({"document-report"})
     *
     * @JMS\Type("AppBundle\Entity\Report\Report")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report", inversedBy="documents")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var Ndr
     *
     * @JMS\Groups({"document-report"})
     *
     * @JMS\Type("AppBundle\Entity\Ndr\Ndr")
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ndr\Ndr")
     * @ORM\JoinColumn(name="ndr_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ndr;

    /**
     * @var ReportSubmission
     *
     * @JMS\Type("AppBundle\Entity\Report\ReportSubmission")
     * @JMS\Groups({"document-report-submission"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\ReportSubmission", inversedBy="documents", cascade={"persist"})
     * @ORM\JoinColumn(name="report_submission_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $reportSubmission;

    /**
     * @var string|null
     * @JMS\Type("string")
     * @JMS\Groups({"document-synchronisation"})
     * @ORM\Column(name="synchronisation_status", type="string", options={"default": null}, nullable=true)
     */
    private $synchronisationStatus;

    /**
     * @var DateTime|null
     * @JMS\Type("DateTime")
     * @JMS\Groups({"document-synchronisation"})
     * @ORM\Column(name="synchronisation_time", type="datetime", options={"default": null}, nullable=true)
     */
    private $synchronisationTime;

    /**
     * @var string|null
     * @JMS\Type("string")
     * @JMS\Groups({"document-synchronisation"})
     * @ORM\Column(name="synchronisation_error", type="string", options={"default": null}, nullable=true)
     */
    private $synchronisationError;

    /**
     * @var User|null
     * @JMS\Type("AppBundle\Entity\User")
     * @JMS\Groups({"document-synchronisation"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="synchronised_by", referencedColumnName="id", onDelete="SET NULL")
     */
    private $synchronisedBy;

    /**
     * Document constructor.
     *
     * Report is initially required, but will be set to null at submission time,
     * and associated to a specific ReportSubmission instead
     *
     * @param mixed $report
     */
    public function __construct($report)
    {
        //TODO create ReportInterface class and use as type hinting
        if ($report instanceof Report) {
            $this->report = $report;
        } elseif ($report instanceof Ndr) {
            $this->ndr = $report;
        }
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
     * @return Ndr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param Report $report
     *
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
     * @param ReportSubmission $reportSubmission
     *
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
     * Is document for OPG admin eyes only
     *
     * @return bool
     */
    public function isAdminDocument()
    {
        return $this->isReportPdf() || $this->isTransactionDocument();
    }

    /**
     * Is document a list of transaction document (admin only)
     *
     * @return bool|int
     */
    private function isTransactionDocument()
    {
        return strpos($this->getFileName(), 'DigiRepTransactions') !== false;
    }

    /**
     * @return string|null
     */
    public function getSynchronisationStatus(): ?string
    {
        return $this->synchronisationStatus;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setSynchronisationStatus(?string $status)
    {
        if (!in_array($status, array(
            self::SYNC_STATUS_QUEUED,
            self::SYNC_STATUS_IN_PROGRESS,
            self::SYNC_STATUS_SUCCESS,
            self::SYNC_STATUS_TEMPORARY_ERROR,
            self::SYNC_STATUS_PERMANENT_ERROR,
        ))) {
            throw new \InvalidArgumentException('Invalid synchronisation status');
        }

        $this->synchronisationStatus = $status;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getSynchronisationTime(): ?DateTime
    {
        return $this->synchronisationTime;
    }

    /**
     * @param DateTime $time
     * @return $this
     */
    public function setSynchronisationTime(?DateTime $time)
    {
        $this->synchronisationTime = $time;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSynchronisationError(): ?string
    {
        return $this->synchronisationError;
    }

    /**
     * @param string $error
     * @return $this
     */
    public function setSynchronisationError(?string $error)
    {
        $this->synchronisationError = $error;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getSynchronisedBy(): ?User
    {
        return $this->synchronisedBy;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setSynchronisedBy(?User $user)
    {
        $this->synchronisedBy = $user;
        return $this;
    }
}
