<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilderResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesGroup;
use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

/**
 * Convert a group of candidates (with the same order UID) to a set of court order entities and relationships
 * between them.
 */
class DeputyshipCandidatesConverter
{
    public function __construct()
    {
    }

    // TODO replace stub functionality with real functionality
    private function findOrderId(string $orderUid): int
    {
        return 0;
    }

    private function insertOrder(array $insertOrder): int
    {
        return 0;
    }

    private function insertOrderDeputy(int $courtOrderId, array $candidate): void
    {
    }

    private function insertOrderReport(int $courtOrderId, array $candidate): void
    {
    }

    private function insertOrderNdr(int $courtOrderId, array $candidate): void
    {
    }

    private function updateOrderStatus(int $courtOrderId, array $candidate): void
    {
    }

    private function updateDeputyStatus(int $courtOrderId, array $candidate): void
    {
    }

    public function createEntitiesFromCandidates(DeputyshipCandidatesGroup $candidatesGroup): DeputyshipBuilderResult
    {
        // could use DeputyProcessingLookupCache for the court order UID lookup
        $insertOrder = $candidatesGroup->insertOrder;
        if (is_null($insertOrder)) {
            $courtOrderId = $this->findOrderId($candidatesGroup->orderUid);
        } else {
            $courtOrderId = $this->insertOrder($insertOrder);
        }

        foreach ($candidatesGroup->getIterator() as $candidate) {
            $action = $candidate['action'];

            if (DeputyshipCandidateAction::InsertOrderDeputy === $action) {
                $this->insertOrderDeputy($courtOrderId, $candidate);
            } elseif (DeputyshipCandidateAction::InsertOrderReport === $action) {
                $this->insertOrderReport($courtOrderId, $candidate);
            } elseif (DeputyshipCandidateAction::InsertOrderNdr === $action) {
                $this->insertOrderNdr($courtOrderId, $candidate);
            } elseif (DeputyshipCandidateAction::UpdateOrderStatus === $action) {
                $this->updateOrderStatus($courtOrderId, $candidate);
            } elseif (DeputyshipCandidateAction::UpdateDeputyStatus === $action) {
                $this->updateDeputyStatus($courtOrderId, $candidate);
            }
        }

        // TODO actually build entities and relationships using $candidatesGroup
        return new DeputyshipBuilderResult(
            outcome: DeputyshipBuilderResultOutcome::EntitiesBuiltSuccessfully,
            message: 'Builder: processed '.$candidatesGroup->totalCandidates().' candidates'
        );
    }
}
