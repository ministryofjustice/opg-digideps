<?php

namespace OPG\Digideps\Backend\Factory;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Entity\StagingDeputyship;
use OPG\Digideps\Backend\Entity\StagingSelectedCandidate;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;

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
        $changes->courtOrderKind = ($csvDeputyship->isHybrid ? CourtOrderKind::Hybrid : CourtOrderKind::Single)->value;
        $changes->reportType = $csvDeputyship->reportType;

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
