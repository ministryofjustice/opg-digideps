<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup;

use JMS\Serializer\Annotation\Exclude;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;

#[Exclude]
final readonly class CleanupPlan
{
    public function __construct(
        public CourtOrder $courtOrder,
        public Report $report,
        public bool $keep,
    ) {
    }

    public function toCsvLine(): string
    {
        $client = $this->courtOrder->getClient();
        return implode(',', [
            $client->getCaseNumber(),
            $this->courtOrder->getCourtOrderUid(),
            $this->report->getId(),
            $this->courtOrder->getOrderMadeDate()->format('Y-m-d'),
            $this->report->getStartDate()->format('Y-m-d'),
            $this->report->getEndDate()->format('Y-m-d'),
            $this->keep ? 'keep' : 'remove'
        ]);
    }
}
