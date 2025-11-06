<?php

namespace App\Entity\Report;

use App\Entity\DocumentInterface;
use App\Entity\Report\Traits\HasReportTrait;
use App\Entity\SynchronisableInterface;
use App\Entity\SynchronisableTrait;
use App\Entity\Traits\CreationAudit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @Assert\Callback(callback="isValidForReport", groups={"document"})
 */
class Document implements DocumentInterface, SynchronisableInterface
{
    use CreationAudit;
    use HasReportTrait;
    use SynchronisableTrait;

    public const FILE_NAME_MAX_LENGTH = 255;

    public function isValidForReport(ExecutionContextInterface $context): void
    {
        $file = $this->getFile();

        if (is_null($file)) {
            return;
        }

        $fileOriginalName = $file->getClientOriginalName();

        if (strlen($fileOriginalName) > self::FILE_NAME_MAX_LENGTH) {
            $context->buildViolation('document.file.errors.maxMessage')->atPath('file')->addViolation();

            return;
        }

        $fileNames = [];
        foreach ($this->getReport()->getDocuments() as $document) {
            $fileNames[] = $document->getFileName();
        }

        if (in_array($fileOriginalName, $fileNames)) {
            $context->buildViolation('document.file.errors.alreadyPresent')->atPath('file')->addViolation();
        }
    }

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"document"})
     */
    private ?int $id = null;

    /**
     * @Assert\NotBlank(message="Please choose a file", groups={"document"})
     * @Assert\File(
     *     maxSize = "15M",
     *     maxSizeMessage = "document.file.errors.maxSizeMessage",
     *     mimeTypes = {"application/pdf", "application/x-pdf", "image/png", "image/jpeg", "image/heif"},
     *     mimeTypesMessage = "document.file.errors.mimeTypesMessage",
     *     groups={"document"}
     * )
     */
    private ?UploadedFile $file = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
     */
    private ?string $fileName = null;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
     */
    private ?string $storageReference = null;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"document"})
     */
    private bool $isReportPdf = false;

    /**
     * @JMS\Type("App\Entity\Report\ReportSubmission")
     * @JMS\Groups({"document-report-subnmission"})
     */
    private ReportSubmission $reportSubmission;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getStorageReference(): ?string
    {
        return $this->storageReference;
    }

    public function setStorageReference(?string $storageReference): self
    {
        $this->storageReference = $storageReference;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function isReportPdf(): bool
    {
        return $this->isReportPdf;
    }

    public function setIsReportPdf(bool $isReportPdf): self
    {
        $this->isReportPdf = $isReportPdf;

        return $this;
    }

    public function getReportSubmission(): ReportSubmission
    {
        return $this->reportSubmission;
    }

    public function setReportSubmission(ReportSubmission $repostSubmission): self
    {
        $this->reportSubmission = $repostSubmission;

        return $this;
    }

    /**
     * Is document for OPG admin eyes only?
     */
    public function isAdminDocument(): bool
    {
        return $this->isReportPdf() || $this->isTransactionDocument();
    }

    /**
     * Is document a list of transaction document (admin only)?
     */
    private function isTransactionDocument(): bool
    {
        return str_contains($this->getFileName() ?? '', 'DigiRepTransactions');
    }
}
