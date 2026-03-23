<?php

namespace App\Entity\Report;

use App\Entity\Traits\CreationAudit;
use App\Entity\User;
use JMS\Serializer\Annotation as JMS;
use RuntimeException;

class ReportSubmission
{
    use CreationAudit;

    #[JMS\Type('integer')]
    private int $id;

    #[JMS\Type('App\Entity\Report\Report')]
    private Report $report;

    /**
     * @var Document[]
     */
    #[JMS\Type('array<App\Entity\Report\Document>')]
    private array $documents = [];

    //#[JMS\Type('App\Entity\User')]
    private ?User $archivedBy = null;

    #[JMS\Type("boolean")]
    private bool $downloadable;

    #[JMS\Type("string")]
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

    public function getReport(): ?Report
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

    public function hasReportPdf(): bool
    {
        foreach ($this->documents as $document) {
            if ($document->isReportPdf()) {
                return true;
            }
        }

        return false;
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

        if (is_null($report)) {
            throw new RuntimeException('Report submission has no associated report');
        }

        /** @var ?\DateTime $startDate */
        $startDate = $report->getStartDate();

        /** @var ?\DateTime $endDate */
        $endDate = $report->getEndDate();

        if (is_null($startDate) || is_null($endDate)) {
            throw new RuntimeException('Report submission is missing start or end date');
        }

        $client = $report->getClient();

        return 'Report_'
            . $client->getCaseNumber()
            . '_' . $startDate->format('Y')
            . '_' . $endDate->format('Y')
            . '_' . $this->getId()
            . '.zip';
    }
}
