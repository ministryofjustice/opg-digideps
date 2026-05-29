<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\Report;

use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Domain\Report\ReportTransitionService;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipChange;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipResult;

final readonly class ReportReassembler
{
    public function __construct(
        private ReportTransitionService $reportTransitionService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function reassembleReport(CourtOrderRelationshipChange $change): CourtOrderRelationshipResult
    {
        return new CourtOrderRelationshipResult($change);
    }
}
