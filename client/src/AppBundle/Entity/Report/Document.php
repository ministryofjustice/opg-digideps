<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\DocumentInterface;
use AppBundle\Entity\Report\Traits\HasReportTrait;
use AppBundle\Entity\SynchronisableInterface;
use AppBundle\Entity\SynchronisableTrait;
use AppBundle\Entity\Traits\CreationAudit;
use AppBundle\Entity\User;
use DateTime;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @Assert\Callback(callback="isValidForReport", groups={"document"})
 */
class Document implements DocumentInterface, SynchronisableInterface
{
    const FILE_NAME_MAX_LENGTH = 255;
    const MAX_UPLOAD_PER_REPORT = 100;

    use CreationAudit;
    use HasReportTrait;
    use SynchronisableTrait;

    /**
     * @param ExecutionContextInterface $context
     */
    public function isValidForReport(ExecutionContextInterface $context): void
    {
        if (!($this->getFile() instanceof UploadedFile)) {
            return;
        }

        $fileNames = [];
        foreach ($this->getReport()->getDocuments() as $document) {
            $fileNames[] = $document->getFileName();
        }

        $fileOriginalName = $this->getFile()->getClientOriginalName();

        if (is_null($fileOriginalName)) {
            $context->buildViolation('document.file.errors.invalidName')->atPath('file')->addViolation();
            return;
        }

        if (strlen($fileOriginalName) > self::FILE_NAME_MAX_LENGTH) {
            $context->buildViolation('document.file.errors.maxMessage')->atPath('file')->addViolation();
            return;
        }

        if (in_array($fileOriginalName, $fileNames)) {
            $context->buildViolation('document.file.errors.alreadyPresent')->atPath('file')->addViolation();
            return;
        }

        if (count($this->getReport()->getDocuments()) >= self::MAX_UPLOAD_PER_REPORT) {
            $context->buildViolation('document.file.errors.maxDocumentsPerReport')->atPath('file')->addViolation();
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
     *     maxSize = "5M",
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
     * @param  int      $id
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
     * @param  string   $fileName
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
     * @param  string   $storageReference
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
     * @return Document
     */
    public function setFile($file)
    {
        $this->file = $file;
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
     * @return $this
     */
    public function setIsReportPdf($isReportPdf)
    {
        $this->isReportPdf = $isReportPdf;
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
     * @return Document
     */
    public function setReportSubmission(ReportSubmission $repostSubmission)
    {
        $this->reportSubmission = $repostSubmission;

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
     * @return bool|int
     */
    private function isTransactionDocument()
    {
        return strpos($this->getFileName(), 'DigiRepTransactions') !== false;
    }
}
