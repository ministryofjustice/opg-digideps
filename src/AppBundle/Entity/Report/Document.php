<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Traits\CreationAudit;
use AppBundle\Entity\Traits\IsSoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
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
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Document
{
    use CreationAudit;
    use IsSoftDeleteableEntity;

    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"audit_log","documents", "document-id"})
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
     */
    private $report;

    /**
     * @var ReportSubmission
     *
     * @JMS\Type("AppBundle\Entity\Report\ReportSubmission")
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\ReportSubmission", inversedBy="documents", cascade={"persist"})
     * @ORM\JoinColumn(name="report_submission_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $reportSubmission;

    /**
     * Document constructor.
     *
     * Report is initially required, but will be set to null at submission time,
     * and associated to a specific ReportSubmission instead
     *
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
//        $report->addDocument($this);
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
     * @param Report|null $report
     *
     * @return $this
     */
    public function setReport(Report $report = null)
    {
        $this->report = $report;
        return $this;
    }

    /**
     * @return ReportSubmission
     */
    public function getReportSubmission()
    {
        return $this->reportSubmission;
    }

    /**
     * @param ReportSubmission $reportSubmission
     * @return Document
     */
    public function setReportSubmission(ReportSubmission $reportSubmission)
    {
        $this->reportSubmission = $reportSubmission;
        $reportSubmission->addDocument($this);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsReportPdf()
    {
        return $this->isReportPdf;
    }

    /**
     * @param boolean $isReportPdf
     * @return Document
     */
    public function setIsReportPdf($isReportPdf)
    {
        $this->isReportPdf = $isReportPdf;

        return $this;
    }

}
