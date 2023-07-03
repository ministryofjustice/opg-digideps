<?php

declare(strict_types=1);

namespace App\Entity\Report;


use App\Entity\Ndr\Ndr;
use JMS\Serializer\Annotation as JMS;

class ImbalanceSummary
{
    /**
     * @JMS\Type("string")
     */
    private string $deputyType;

    /**
     * @JMS\Type("integer")
     */
    private int $noImbalance;

    /**
     * @JMS\Type("integer")
     */
    private int $imbalanceReported;

    /**
     * @JMS\Type("integer")
     */
    private int $imbalancePercent;

    /**
     * @JMS\Type("integer")
     */
    private int $totalSubmitted;

    /**
     * @JMS\Type("App\Entity\Report\Report")
     */
    private ?Report $report = null;

    /**
     * @JMS\Type("App\Entity\Ndr\Ndr")
     */
    private ?Ndr $ndr = null;

    public function getDeputyType(): string
    {
        return $this->deputyType;
    }

    public function setDeputyType(string $deputyType): void
    {
        $this->deputyType = $deputyType;
    }

    public function getNoImbalance(): int
    {
        return $this->noImbalance;
    }

    public function setNoImbalance(int $noImbalance): void
    {
        $this->noImbalance = $noImbalance;
    }

    public function getImbalanceReported(): int
    {
        return $this->imbalanceReported;
    }

    public function setImbalanceReported(int $imbalanceReported): void
    {
        $this->imbalanceReported = $imbalanceReported;
    }

    public function getImbalancePercent(): int
    {
        return $this->imbalancePercent;
    }

    public function setImbalancePercent(int $imbalancePercent): void
    {
        $this->imbalancePercent = $imbalancePercent;
    }

    public function getTotalSubmitted(): int
    {
        return $this->totalSubmitted;
    }

    public function setTotalSubmitted(int $totalSubmitted): void
    {
        $this->totalSubmitted = $totalSubmitted;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): void
    {
        $this->report = $report;
    }

    public function getNdr(): ?Ndr
    {
        return $this->ndr;
    }

    public function setNdr(?Ndr $ndr): void
    {
        $this->ndr = $ndr;
    }
}
