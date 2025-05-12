<?php

namespace App\Factory;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;

class StagingSelectedCandidateFactory
{
    public function createUpdateOrderStatusCandidate(StagingDeputyship $csvDeputyship, int $courtOrderId): StagingSelectedCandidate
    {
        $changes = new StagingSelectedCandidate();
        $changes->action = StagingSelectedCandidate::UPDATE_ORDER_STATUS;

        $changes->orderUid = $csvDeputyship->orderUid;
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
        $changes = new StagingSelectedCandidate();
        $changes->action = StagingSelectedCandidate::UPDATE_DEPUTY_STATUS;

        $changes->orderUid = $csvDeputyship->orderUid;
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
        $changes = new StagingSelectedCandidate();
        $changes->action = StagingSelectedCandidate::INSERT_ORDER_DEPUTY;

        $changes->orderUid = $csvDeputyship->orderUid;
        $changes->deputyUid = $csvDeputyship->deputyUid;
        $changes->deputyStatusOnOrder = $csvDeputyship->deputyIsActiveOnOrder();

        $changes->deputyId = $deputyId;

        return $changes;
    }

    public function createInsertOrderCandidate(StagingDeputyship $csvDeputyship, int $clientId): StagingSelectedCandidate
    {
        $changes = new StagingSelectedCandidate();
        $changes->action = StagingSelectedCandidate::INSERT_ORDER;

        $changes->orderUid = $csvDeputyship->orderUid;
        $changes->deputyUid = $csvDeputyship->deputyUid;
        $changes->orderType = $csvDeputyship->orderType;
        $changes->status = $csvDeputyship->orderStatus;
        $changes->orderMadeDate = $csvDeputyship->orderMadeDate;

        $changes->clientId = $clientId;

        return $changes;
    }

    public function createInsertOrderReportCandidate(string $orderUid, int $reportId): StagingSelectedCandidate
    {
        $changes = new StagingSelectedCandidate();
        $changes->action = StagingSelectedCandidate::INSERT_ORDER_REPORT;

        $changes->orderUid = $orderUid;
        $changes->reportId = $reportId;

        return $changes;
    }

    public function createInsertOrderNdrCandidate(string $orderUid, int $ndrId): StagingSelectedCandidate
    {
        $changes = new StagingSelectedCandidate();
        $changes->action = StagingSelectedCandidate::INSERT_ORDER_NDR;

        $changes->orderUid = $orderUid;
        $changes->ndrId = $ndrId;

        return $changes;
    }
}
