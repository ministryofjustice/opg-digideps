<?php

namespace App\Factory;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\Service\ReportUtils;

class StagingSelectedCandidateFactory
{
    public function __construct(
        private readonly ReportUtils $reportUtils,
    ) {
    }

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

    // NB this will be used to both insert a report and associate it with a court order
    public function createInsertReportCandidate(
        string $orderUid, string $reportType, string $orderType, string $deputyType, string $orderMadeDate,
    ): StagingSelectedCandidate {
        $changes = new StagingSelectedCandidate();
        $changes->action = StagingSelectedCandidate::INSERT_REPORT;

        $changes->orderUid = $orderUid;
        $changes->reportType = $this->reportUtils->determineReportType($reportType, $orderType, $deputyType);
        $changes->orderMadeDate = $orderMadeDate;

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
}
