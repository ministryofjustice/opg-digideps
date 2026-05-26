<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Report;

use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;

final class ReportTransitionResult
{
    public function __construct(
        /** @var string[] $messages */
        public array $messages = [],
        /** @var string[] $errorMessages */
        public array $errorMessages = [],
        // true if any report changed its type, any report was added or removed,
        // or relationships to court orders were changed
        public bool $transitioned = false,
        /** @var array<Report> $updatedReports */
        public array $updatedReports = [],
        /** @var array<CourtOrder> $updatedCourtOrders */
        public array $updatedCourtOrders = [],
        /** @var array<Report> $removedReports */
        public array $removedReports = [],
    ) {
    }
}
