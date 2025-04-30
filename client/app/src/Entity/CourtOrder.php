<?php

namespace App\Entity;

use App\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

/**
 * Court Orders for clients.
 */
class CourtOrder
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $courtOrderUid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $orderType;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $status;

    /**
     * @JMS\Type("array<App\Entity\Deputy>")
     *
     * @var Deputy[]
     */
    private array $activeDeputies = [];

    /**
     * @JMS\Type("array<App\Entity\Report\Report>")
     *
     * @var Report[]
     */
    private array $reports = [];

    /**
     * @JMS\Type("App\Entity\Client")
     */
    private Client $client;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): CourtOrder
    {
        $this->id = $id;

        return $this;
    }

    public function getCourtOrderUid(): string
    {
        return $this->courtOrderUid;
    }

    public function setCourtOrderUid(string $courtOrderUid): CourtOrder
    {
        $this->courtOrderUid = $courtOrderUid;

        return $this;
    }

    public function getOrderType(): string
    {
        return $this->orderType;
    }

    public function setOrderType(string $orderType): CourtOrder
    {
        $this->orderType = $orderType;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): CourtOrder
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return array $reports
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * @param Report[] $reports
     */
    public function setReports(array $reports): CourtOrder
    {
        $this->reports = $reports;

        return $this;
    }

    /**
     * @return Deputy[]
     */
    public function getActiveDeputies(): array
    {
        return $this->activeDeputies;
    }

    /**
     * @param Deputy[] $activeDeputies
     */
    public function setActiveDeputies(array $activeDeputies): CourtOrder
    {
        $this->activeDeputies = $activeDeputies;

        return $this;
    }

    public function getActiveReport(): ?Report
    {
        foreach ($this->reports as $report) {
            if (!$report->isSubmitted() && !$report->getUnSubmitDate()) {
                return $report;
            }
        }

        return null;
    }

    public function getUnsubmittedReport(): ?Report
    {
        foreach ($this->reports as $report) {
            if (!$report->isSubmitted() && $report->getUnSubmitDate()) {
                return $report;
            }
        }

        return null;
    }

    /**
     * @return Report[]
     */
    public function getSubmittedReports(): array
    {
        return array_values(array_filter($this->reports, fn ($report) => $report->isSubmitted()));
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function hasCoDeputies(): bool
    {
        return count($this->activeDeputies) > 1;
    }

    /**
     * @return Deputy[]
     */
    public function getCoDeputies(string $loggedInDeputyUid): array
    {
        $deputies = array_filter($this->activeDeputies, fn ($deputy) => $deputy->getDeputyUid() !== $loggedInDeputyUid);
        uasort($deputies, fn ($deputyA, $deputyB) => strcmp($deputyA->getFirstName(), $deputyB->getFirstName()));

        return array_values($deputies);
    }
}
