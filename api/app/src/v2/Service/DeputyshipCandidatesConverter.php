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

    public function convert(DeputyshipCandidatesGroup $candidatesGroup, bool $dryRun): DeputyshipBuilderResult
    {
        $this->dbAccess->beginTransaction();

        $buildResult = new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::CandidatesApplied);

        $insertOrder = $candidatesGroup->insertOrder;
        if (!is_null($insertOrder)) {
            $result = $this->dbAccess->insertOrder($insertOrder);

            if (!$result->success) {
                $this->dbAccess->rollback();

                $errors = [];
                if (!is_null($result->error)) {
                    $errors[] = $result->error;
                }

                return new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::InsertOrderFailed, $errors);
            }

            $buildResult->addCandidateResult($result);
        }

        $result = $this->dbAccess->findOrderId($candidatesGroup->orderUid);

        // court order could not be found
        if (!$result->success) {
            $this->dbAccess->rollback();

            $errors = [];
            if (!is_null($result->error)) {
                $errors[] = $result->error;
            }

            return new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::NoExistingOrder, $errors);
        }

        /** @var int $courtOrderId */
        $courtOrderId = $result->data;

        $failed = false;

        foreach ($candidatesGroup->getIterator() as $candidate) {
            $action = $candidate['action'];

            if (DeputyshipCandidateAction::InsertOrderDeputy === $action) {
                $result = $this->dbAccess->insertOrderDeputy($courtOrderId, $candidate);
            } elseif (DeputyshipCandidateAction::InsertOrderReport === $action) {
                $result = $this->dbAccess->insertOrderReport($courtOrderId, $candidate);
            } elseif (DeputyshipCandidateAction::InsertOrderNdr === $action) {
                $result = $this->dbAccess->insertOrderNdr($courtOrderId, $candidate);
            } elseif (DeputyshipCandidateAction::UpdateOrderStatus === $action) {
                $result = $this->dbAccess->updateOrderStatus($courtOrderId, $candidate);
            } elseif (DeputyshipCandidateAction::UpdateDeputyStatus === $action) {
                $result = $this->dbAccess->updateDeputyStatus($courtOrderId, $candidate);
            }

            $buildResult->addCandidateResult($result);

            $failed = !$result->success;

            if ($failed) {
                break;
            }
        }

        if ($failed || $dryRun) {
            $this->dbAccess->rollback();
        } else {
            $this->dbAccess->endTransaction();
        }

        return $buildResult;
    }
}
