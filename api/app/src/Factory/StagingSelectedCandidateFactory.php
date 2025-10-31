<?php

namespace App\Factory;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

class StagingSelectedCandidateFactory
{
    public function createUpdateOrderStatusCandidate(StagingDeputyship $csvDeputyship, int $courtOrderId): StagingSelectedCandidate
    {
        $changes = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateOrderStatus, $csvDeputyship->orderUid);

        $changes->deputyUid = $csvDeputyship->deputyUid;
        $changes->status = $csvDeputyship->orderStatus;

        $changes->orderId = $courtOrderId;

        return $changes;
    }

    public function createUpdateDeputyStatusCandidate(
        StagingDeputyship $csvDeputyship,
        int $deputyId,
        int $courtOrderId,
    ): StagingSelectedCandidate {
        $changes = new StagingSelectedCandidate(DeputyshipCandidateAction::UpdateDeputyStatus, $csvDeputyship->orderUid);

        $changes->deputyUid = $csvDeputyship->deputyUid;
        $changes->deputyStatusOnOrder = $csvDeputyship->deputyIsActiveOnOrder();

        $changes->orderId = $courtOrderId;
        $changes->deputyId = $deputyId;

        return $changes;
    }

    public function createInsertOrderDeputyCandidate(
        StagingDeputyship $csvDeputyship,
        int $deputyId,
    ): StagingSelectedCandidate {
        $changes = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderDeputy, $csvDeputyship->orderUid);

        $changes->deputyUid = $csvDeputyship->deputyUid;
        $changes->deputyStatusOnOrder = $csvDeputyship->deputyIsActiveOnOrder();

        $changes->deputyId = $deputyId;

        return $changes;
    }

    public function createInsertOrderCandidate(StagingDeputyship $csvDeputyship, int $clientId): StagingSelectedCandidate
    {
        $changes = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrder, $csvDeputyship->orderUid);

        $changes->orderType = $csvDeputyship->orderType;
        $changes->status = $csvDeputyship->orderStatus;
        $changes->orderMadeDate = $csvDeputyship->orderMadeDate;

        $changes->clientId = $clientId;

        return $changes;
    }

    public function createInsertOrderReportCandidate(string $orderUid, int $reportId): StagingSelectedCandidate
    {
        $changes = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrderReport, $orderUid);

        $changes->reportId = $reportId;

        return $changes;
    }
}
