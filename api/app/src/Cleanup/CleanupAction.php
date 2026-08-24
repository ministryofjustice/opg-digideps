<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;

final readonly class CleanupAction implements \Stringable
{
    public function __construct(
        public int $clientId,
        public int $reportId,
        public int $courtOrderId,
        public string $reportType,
        public string $reportStart,
        public string $reportEnd,
        public string $madeDate,
        public string $caseNumber,
        public string $courtOrderUid,
        public bool $keep,
        public bool $orderActive,
        public string $reportSubmission,
        public string $reportUnSubmission,
    ) {
    }

    public static function from(CourtOrder $courtOrder, Report $report, bool $keep): CleanupAction
    {
        return new CleanupAction(
            $courtOrder->getClient()->getId(),
            $report->getId(),
            $courtOrder->getId(),
            $report->getType(),
            $report->getStartDate()->format('Y-m-d'),
            $report->getEndDate()->format('Y-m-d'),
            $courtOrder->getOrderMadeDate()->format('Y-m-d'),
            $courtOrder->getClient()->getCaseNumber() ?? '?',
            $courtOrder->getCourtOrderUid(),
            $keep,
            $courtOrder->getStatus() === 'Active',
            $report->getSubmitDate()?->format('Y-m-d') ?? '',
            $report->getUnSubmitDate()?->format('Y-m-d') ?? ''
        );
    }

    public function __toString(): string
    {
        return implode(',', array_map(fn (string $value) => "\"{$value}\"", [
            $this->caseNumber,
            $this->courtOrderUid,
            (string)$this->reportId,
            $this->reportType,
            $this->keep ? 'KEEP' : 'REMOVE',
            $this->madeDate,
            $this->reportStart,
            $this->reportEnd,
            $this->reportSubmission,
            $this->reportUnSubmission,
            $this->orderActive ? 'ACTIVE' : 'INACTIVE',
        ]));
    }
}
