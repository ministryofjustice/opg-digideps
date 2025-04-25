<?php

namespace App\Factory;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;

class StagingSelectedCandidateFactory
{
    public const UPDATE_ORDER_STATUS = 'UPDATE ORDER STATUS';
    public const UPDATE_DEPUTY_STATUS = 'UPDATE DEPUTY STATUS ON ORDER';
    public const INSERT_ORDER_DEPUTY = 'INSERT ORDER DEPUTY';
    public const INSERT_ORDER = 'INSERT ORDER';

    public function createUpdateOrderStatusCandidate(StagingDeputyship $csvDeputyship, int $courtOrderId): StagingSelectedCandidate
    {
        $changes = new StagingSelectedCandidate();
        $changes->action = self::UPDATE_ORDER_STATUS;
        $changes->orderUid = $csvDeputyship->orderUid;
        $changes->deputyUid = $csvDeputyship->deputyUid;
        $changes->orderId = $courtOrderId;
        $changes->status = $csvDeputyship->orderStatus;

        return $changes;
    }

    public function createUpdateDeputyStatusCandidate(
        StagingDeputyship $csvDeputyship,
        int $deputyId,
        int $courtOrderId,
    ): StagingSelectedCandidate {
        $changes = new StagingSelectedCandidate();
        $changes->action = self::UPDATE_DEPUTY_STATUS;
        $changes->orderUid = $csvDeputyship->orderUid;
        $changes->orderId = $courtOrderId;
        $changes->deputyId = $deputyId;
        $changes->deputyUid = $csvDeputyship->deputyUid;
        $changes->deputyStatusOnOrder = $csvDeputyship->deputyIsActiveOnOrder();

        return $changes;
    }

    public function createInsertOrderDeputyCandidate(
        StagingDeputyship $csvDeputyship,
        int $deputyId,
    ): StagingSelectedCandidate {
        $changes = new StagingSelectedCandidate();
        $changes->action = self::INSERT_ORDER_DEPUTY;
        $changes->orderUid = $csvDeputyship->orderUid;
        $changes->deputyId = $deputyId;
        $changes->deputyUid = $csvDeputyship->deputyUid;
        $changes->deputyStatusOnOrder = $csvDeputyship->deputyIsActiveOnOrder();

        return $changes;
    }

    public function createInsertOrderCandidate(StagingDeputyship $csvDeputyship, int $clientId): StagingSelectedCandidate
    {
        $changes = new StagingSelectedCandidate();
        $changes->action = self::INSERT_ORDER;
        $changes->orderUid = $csvDeputyship->orderUid;
        $changes->orderType = $csvDeputyship->orderType;
        $changes->status = $csvDeputyship->orderStatus;
        $changes->clientId = $clientId;
        $changes->orderMadeDate = $csvDeputyship->orderMadeDate;
        $changes->deputyUid = $csvDeputyship->deputyUid;

        return $changes;
    }
}
