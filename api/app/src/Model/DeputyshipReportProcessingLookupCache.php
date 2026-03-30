<?php

declare(strict_types=1);

namespace App\Model;

use App\Repository\ReportRepository;

class DeputyshipReportProcessingLookupCache
{
    private bool $isInitialised = false;

    /** @var array<string, int>  */
    private array $latestReportIdToCourtOrderUid;

    /** @var array<string, string>  */
    private array $latestReportTypeToId;

    public function __construct(private readonly ReportRepository $reportRepository)
    {
    }

    public function init(): bool
    {
        if ($this->isInitialised) {
            return $this->isInitialised;
        }

        $latestReports = $this->reportRepository->findCourtOrdersLatestReport();
        $this->latestReportIdToCourtOrderUid = array_column($latestReports, 'id', 'court_order_uid');
        $this->latestReportTypeToId = array_column($latestReports, 'type', 'id');

        $this->isInitialised = true;

        return $this->isInitialised;
    }

    private function throwIfNotInitialised(): void
    {
        if (!$this->isInitialised) {
            throw new \RuntimeException($this::class . ' is not initialised');
        }
    }

    public function getLatestReportIdForCourtOrderUid(string $uid): ?int
    {
        $this->throwIfNotInitialised();

        return $this->latestReportIdToCourtOrderUid[$uid] ?? null;
    }

    public function getLatestReportTypeForId(int $id): ?string
    {
        $this->throwIfNotInitialised();

        return $this->latestReportTypeToId[$id] ?? null;
    }
}
