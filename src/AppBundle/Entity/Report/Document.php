<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Traits\CreationAudit;
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
 *
 */
class Document
{
    use CreationAudit;

    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"audit_log","documents"})
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
     * @ORM\Column(name="local_filename", type="string", length=150, nullable=false)
     */
    private $localFilename;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"documents"})
     *
     * @ORM\Column(name="uploaded_filename", type="string", length=150, nullable=true)
     */
    private $uploadedFilename;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"documents"})
     *
     * @ORM\Column(name="upload_reference", type="string", length=150, nullable=true)
     */
    private $uploadReference;

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
     * Document constructor.
     *
     * @param Report $report
     */
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

    /**
     * @return string
     */
    public function getLocalFilename()
    {
        return $this->localFilename;
    }

    /**
     * @param string $localFilename
     *
     * @return $this
     */
    public function setLocalFilename($localFilename)
    {
        $this->localFilename = $localFilename;
        return $this;
    }

    /**
     * @return string
     */
    public function getUploadedFilename()
    {
        return $this->uploadedFilename;
    }

    /**
     * @param string $uploadedFilename
     *
     * @return $this
     */
    public function setUploadedFilename($uploadedFilename)
    {
        $this->uploadedFilename = $uploadedFilename;
        return $this;
    }

    /**
     * @return string
     */
    public function getUploadReference()
    {
        return $this->uploadReference;
    }

    /**
     * @param string $uploadReference
     *
     * @return $this
     */
    public function setUploadReference($uploadReference)
    {
        $this->uploadReference = $uploadReference;
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
     *
     * @return $this
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
        return $this;
    }
}
