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
        $result = new CourtOrderRelationshipResult($change);

        $transitionResult = $this->reportTransitionService->transitionReports($change);
        if ($transitionResult === null) {
            return $result;
        }

        if ($transitionResult->hasErrors()) {
            $result->appendError(implode('; ', $transitionResult->errorMessages));
            return $result;
        }

        foreach ($transitionResult->updatedReports as $updatedReport) {
            $this->entityManager->persist($updatedReport);
        }

        foreach ($transitionResult->updatedCourtOrders as $updatedCourtOrder) {
            $this->entityManager->persist($updatedCourtOrder);
        }

        $this->entityManager->flush();

        $result->appendMessage(implode('; ', array_map(fn (callable|string $message): string => is_string($message) ? $message : $message(), $transitionResult->messages)));

        return $result;
    }
}
