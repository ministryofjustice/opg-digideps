<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class ReportSubmissionSummary
{
    #[JMS\Type('integer')]
    private ?int $id = null;

    #[JMS\Type('string')]
    private ?string $caseNumber = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    private ?\DateTime $dateReceived = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    private ?\DateTime $scanDate = null;

    #[JMS\Type('string')]
    private ?string $formType = null;

    #[JMS\Type('string')]
    private ?string $documentType = null;

    #[JMS\Type('string')]
    private ?string $documentId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(?string $caseNumber): static
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getDateReceived(): ?\DateTime
    {
        return $this->dateReceived;
    }

    public function setDateReceived(?\DateTime $dateReceived): static
    {
        $this->dateReceived = $dateReceived;

        return $this;
    }

    public function getScanDate(): ?\DateTime
    {
        return $this->scanDate;
    }

    public function setScanDate(?\DateTime $scanDate): static
    {
        $this->scanDate = $scanDate;

        return $this;
    }

    public function getFormType(): ?string
    {
        return $this->formType;
    }

    public function setFormType(?string $formType): static
    {
        $this->formType = $formType;

        return $this;
    }

    public function getDocumentType(): ?string
    {
        return $this->documentType;
    }

    public function setDocumentType(?string $documentType): static
    {
        $this->documentType = $documentType;

        return $this;
    }

    public function getDocumentId(): ?string
    {
        return $this->documentId;
    }

    public function setDocumentId(?string $documentId): static
    {
        $this->documentId = $documentId;

        return $this;
    }
}
