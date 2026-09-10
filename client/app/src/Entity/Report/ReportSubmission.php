<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Traits\CreationAudit;
use OPG\Digideps\Frontend\Entity\User;

class ReportSubmission
{
    use CreationAudit;

    #[JMS\Type('integer')]
    private int $id;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Report')]
    private Report $report;

    /**
     * @var Document[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Document>')]
    private array $documents = [];

    #[JMS\Type('OPG\Digideps\Frontend\Entity\User')]
    private ?User $archivedBy = null;

    #[JMS\Type('boolean')]
    private bool $downloadable;

    #[JMS\Type('string')]
    private ?string $uuid;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;
        return $this;
    }

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @param Document[] $documents
     */
    public function setDocuments(array $documents): static
    {
        $this->documents = $documents;
        return $this;
    }

    public function getArchivedBy(): ?User
    {
        return $this->archivedBy;
    }

    public function setArchivedBy(?User $archivedBy): static
    {
        $this->archivedBy = $archivedBy;
        return $this;
    }

    public function isDownloadable(): bool
    {
        return $this->downloadable;
    }

    public function setDownloadable(bool $downloadable): static
    {
        $this->downloadable = $downloadable;
        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getZipName(): string
    {
        $report = $this->getReport();
        $client = $report->getClient();

        return 'Report_'
            . $client->getCaseNumber()
            . '_' . $report->getStartDate()->format('Y')
            . '_' . $report->getEndDate()->format('Y')
            . '_' . $this->getId()
            . '.zip';
    }
}
