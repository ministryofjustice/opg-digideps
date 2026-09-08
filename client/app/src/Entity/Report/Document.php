<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use OPG\Digideps\Frontend\Entity\SynchronisableInterface;
use OPG\Digideps\Frontend\Entity\SynchronisableTrait;
use OPG\Digideps\Frontend\Entity\Traits\CreationAudit;
use OPG\Digideps\Frontend\Service\File\FileNameManipulation;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback(callback: 'isValidForReport', groups: ['document'])]
class Document implements SynchronisableInterface
{
    use CreationAudit;
    use HasReportTrait;
    use SynchronisableTrait;

    public const int FILE_NAME_MAX_LENGTH = 255;

    public function isValidForReport(ExecutionContextInterface $context): bool
    {
        if (!($this->getFile() instanceof UploadedFile)) {
            return false;
        }

        $fileOriginalName = FileNameManipulation::fileNameSanitation($this->getFile()->getClientOriginalName());

        if (empty($fileOriginalName)) {
            $context->buildViolation('document.file.errors.invalidName')->atPath('file')->addViolation();

            return false;
        }

        if (strlen($fileOriginalName) > self::FILE_NAME_MAX_LENGTH) {
            $context->buildViolation('document.file.errors.maxMessage')->atPath('file')->addViolation();

            return false;
        }

        $fileNames = [];
        foreach ($this->getReport()->getDocuments() as $document) {
            $fileNames[] = $document->getFileName();
        }

        if (in_array($fileOriginalName, $fileNames)) {
            $context->buildViolation('document.file.errors.alreadyPresent')->atPath('file')->addViolation();
            return false;
        }

        return true;
    }

    #[JMS\Type('integer')]
    #[JMS\Groups(['document'])]
    private ?int $id = null;

    /**
     * (add more validators here if needed)
     * http://symfony.com/doc/current/reference/constraints/File.html
     */
    #[Assert\NotBlank(message: 'Please choose a file', groups: ['document'])]
    #[Assert\File(maxSize: '15M', mimeTypes: ['application/pdf', 'application/x-pdf', 'image/png', 'image/jpeg', 'image/heif'], maxSizeMessage: 'document.file.errors.maxSizeMessage', mimeTypesMessage: 'document.file.errors.mimeTypesMessage', groups: ['document'])]
    private ?UploadedFile $file = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['document'])]
    private ?string $fileName = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['document'])]
    private ?string $storageReference = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['document'])]
    private ?bool $isReportPdf = null;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\ReportSubmission')]
    #[JMS\Groups(['document-report-subnmission'])]
    private ?ReportSubmission $reportSubmission = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): static
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

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): static
    {
        $this->file = $file;
        return $this;
    }

    public function isReportPdf(): ?bool
    {
        return $this->isReportPdf;
    }

    public function setIsReportPdf(?bool $isReportPdf): static
    {
        $this->isReportPdf = $isReportPdf;
        return $this;
    }

    public function getReportSubmission(): ?ReportSubmission
    {
        return $this->reportSubmission;
    }

    public function setReportSubmission(?ReportSubmission $repostSubmission): static
    {
        $this->reportSubmission = $repostSubmission;
        return $this;
    }

    /**
     * For OPG admin only
     */
    public function isAdminDocument(): bool
    {
        return $this->isReportPdf() || $this->isTransactionDocument();
    }

    /**
     * Is document a list of transactions document (admin only)
     */
    private function isTransactionDocument(): bool
    {
        return str_contains($this->getFileName() ?? '', 'DigiRepTransactions');
    }
}
