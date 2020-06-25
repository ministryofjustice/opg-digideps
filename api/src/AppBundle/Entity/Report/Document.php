<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\SynchronisableInterface;
use AppBundle\Entity\SynchronisableTrait;
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
class Document implements SynchronisableInterface
{
    use CreationAudit;
    use SynchronisableTrait;

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
}
