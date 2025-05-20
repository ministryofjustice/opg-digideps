<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Model\DeputyshipProcessingRawDbAccess;
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
    public function __construct(
        private readonly DeputyshipProcessingRawDbAccess $dbAccess,
    ) {
    }

    public function createEntitiesFromCandidates(DeputyshipCandidatesGroup $candidatesGroup): DeputyshipBuilderResult
    {
        $this->dbAccess->beginTransaction();

        $buildResult = new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::CandidatesApplied);

        $insertOrder = $candidatesGroup->insertOrder;
        if (!is_null($insertOrder)) {
            $insertedOrderOk = $this->dbAccess->insertOrder($insertOrder);
            if ($insertedOrderOk) {
                $buildResult->addCandidateResult(success: true);
            } else {
                $this->dbAccess->rollback();

                return new DeputyshipBuilderResult(
                    DeputyshipBuilderResultOutcome::InsertOrderFailed,
                    [sprintf('could not insert court order with UID %s', $candidatesGroup->orderUid)]
                );
            }
        }

        $courtOrderId = $this->dbAccess->findOrderId($candidatesGroup->orderUid);

        // court order could not be found
        if (is_null($courtOrderId)) {
            return new DeputyshipBuilderResult(
                DeputyshipBuilderResultOutcome::NoExistingOrder,
                [sprintf('could not find court order with UID %s', $candidatesGroup->orderUid)]
            );
        }

        foreach ($candidatesGroup->getIterator() as $candidate) {
            $action = $candidate['action'];

            if (DeputyshipCandidateAction::InsertOrderDeputy === $action) {
                $buildResult->addCandidateResult(
                    $this->dbAccess->insertOrderDeputy($courtOrderId, $candidate),
                    sprintf('insert order deputy not applied for court order UID %s', $candidatesGroup->orderUid)
                );
            } elseif (DeputyshipCandidateAction::InsertOrderReport === $action) {
                $buildResult->addCandidateResult(
                    $this->dbAccess->insertOrderReport($courtOrderId, $candidate),
                    sprintf('insert order report not applied for court order UID %s', $candidatesGroup->orderUid)
                );
            } elseif (DeputyshipCandidateAction::InsertOrderNdr === $action) {
                $buildResult->addCandidateResult(
                    $this->dbAccess->insertOrderNdr($courtOrderId, $candidate),
                    sprintf('insert order ndr not applied for court order UID %s', $candidatesGroup->orderUid)
                );
            } elseif (DeputyshipCandidateAction::UpdateOrderStatus === $action) {
                $buildResult->addCandidateResult(
                    $this->dbAccess->updateOrderStatus($courtOrderId, $candidate),
                    sprintf('update order status not applied for court order UID %s', $candidatesGroup->orderUid)
                );
            } elseif (DeputyshipCandidateAction::UpdateDeputyStatus === $action) {
                $buildResult->addCandidateResult(
                    $this->dbAccess->updateDeputyStatus($courtOrderId, $candidate),
                    sprintf('update deputy status on order not applied for court order UID %s', $candidatesGroup->orderUid)
                );
            }
        }

        $this->dbAccess->endTransaction();

        return $buildResult;
    }
}
