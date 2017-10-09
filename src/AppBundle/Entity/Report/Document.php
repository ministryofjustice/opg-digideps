<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use AppBundle\Entity\Traits\CreationAudit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(methods={"isValidForReport"}, groups={"document"})
 */
class Document
{
    const FILE_NAME_MAX_LENGTH = 255;
    const MAX_UPLOAD_PER_REPORT = 100;

    use CreationAudit;
    use HasReportTrait;

    /**
     * @param ExecutionContextInterface $context
     */
    public function isValidForReport(ExecutionContextInterface $context)
    {
        if (empty($this->getFile()) || !($this->getFile() instanceof UploadedFile)) {
            return;
        }

        $fileNames = [];
        foreach ($this->getReport()->getDocuments() as $document) {
            $fileNames[] = $document->getFileName();
        }

        $fileOriginalName = $this->getFile()->getClientOriginalName();

        if (strlen($fileOriginalName) > self::FILE_NAME_MAX_LENGTH) {
            $context->addViolationAt('file', 'document.file.errors.maxMessage');
            return;
        }

        if (in_array($fileOriginalName, $fileNames)) {
            $context->addViolationAt('file', 'document.file.errors.alreadyPresent');
            return;
        }

        if (count($this->getReport()->getDocuments()) >= self::MAX_UPLOAD_PER_REPORT) {
            $context->addViolationAt('file', 'document.file.errors.maxDocumentsPerReport');
            return;
        }
    }


    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"document"})
     */
    private $id;

    /**
     * // add more validators here if needed
     * http://symfony.com/doc/current/reference/constraints/File.html
     *
     * @Assert\NotBlank(message="Please choose a file", groups={"document"})
     * @Assert\File(
     *     maxSize = "15M",
     *     maxSizeMessage = "document.file.errors.maxSizeMessage",
     *     mimeTypes = {"application/pdf", "application/x-pdf", "image/png", "image/jpeg"},
     *     mimeTypesMessage = "document.file.errors.mimeTypesMessage",
     *     groups={"document"}
     * )
     *
     * @var UploadedFile
     */
    private $file;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
     *
     * @var string
     */
    private $fileName;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
     *
     * @var string
     */
    private $storageReference;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"document"})
     */
    private $isReportPdf;

    /**
     * @var ReportSubmission
     *
     * @JMS\Type("AppBundle\Entity\Report\ReportSubmission")
     * @JMS\Groups({"document-report-subnmission"})
     */
    private $reportSubmission;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Document
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
     * @return Document
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
     * @return Document
     */
    public function setStorageReference($storageReference)
    {
        $this->storageReference = $storageReference;

        return $this;
    }


    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return boolean
     */
    public function isReportPdf()
    {
        return $this->isReportPdf;
    }

    /**
     * @param boolean $isReportPdf
     */
    public function setIsReportPdf($isReportPdf)
    {
        $this->isReportPdf = $isReportPdf;
    }

    /**
     * @return ReportSubmission
     */
    public function getReportSubmission()
    {
        return $this->reportSubmission;
    }
}
