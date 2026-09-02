<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Report;

use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderPair;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\CourtOrderRepository;
use OPG\Digideps\Backend\Service\ReportService;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipChange;

/**
 * Transition a report from its old report type to its new one, splitting/merging/removing reports as necessary.
 */
final readonly class ReportTransitionService
{
    public function __construct(
        private ReportService $reportService,
        private CourtOrderRepository $courtOrderRepository,
    ) {
    }

    public function transitionReports(CourtOrderRelationshipChange $courtOrderChange): ?ReportTransitionResult
    {
        if (
            !($courtOrderChange->hasKindChange() || $courtOrderChange->hasSiblingIdChange()) ||
            $courtOrderChange->currentSiblingId === null
        ) {
            return null;
        }

        $result = new ReportTransitionResult();

        $courtOrder = $this->courtOrderRepository->find($courtOrderChange->courtOrderId);
        if ($courtOrder === null) {
            $result->errorMessages[] = 'Could not find main transition court order with ID ' . $courtOrderChange->courtOrderId;
            return $result;
        }

        $currentSiblingCourtOrder = $this->courtOrderRepository->find($courtOrderChange->currentSiblingId);
        if ($currentSiblingCourtOrder === null) {
            $result->errorMessages[] = 'Could not find sibling transition court order with ID ' . $courtOrderChange->currentSiblingId;
            return $result;
        }

        try {
            $courtOrderPair = CourtOrderPair::create($courtOrder, $currentSiblingCourtOrder);
        } catch (\DomainException $exception) {
            $result->errorMessages[] = $exception->getMessage();
            return $result;
        }

        // if we are working from a dual or hybrid to hybrid or dual respectively, we only need to work from
        // one side of the pair, so constrain transitions to the PFA side
        $isPfa = ($courtOrder->getOrderType() === CourtOrderType::PFA);

        $oldCourtOrderKind = $courtOrderChange->oldKind;
        $newCourtOrderKind = $courtOrderChange->currentKind;

        if ($isPfa && $oldCourtOrderKind === CourtOrderKind::Hybrid && $newCourtOrderKind === CourtOrderKind::Dual) {
            $result = $this->hybridToDual($courtOrderPair, $courtOrderChange);
        }

        if ($isPfa && $oldCourtOrderKind === CourtOrderKind::Dual && $newCourtOrderKind === CourtOrderKind::Hybrid) {
            $result = $this->dualToHybrid($courtOrderPair, $courtOrderChange);
        }

        // in single to dual scenarios, we have to work from HW or PFA, as there is only one source court order
        if ($oldCourtOrderKind === CourtOrderKind::Single && $newCourtOrderKind === CourtOrderKind::Dual) {
            $result = $this->singleToDual($courtOrderPair);
        }

        return $result;
    }

    /**
     * Hybrid -> Dual
     *
     * A hybrid report is already associated with two court orders. Keep the link to the court order
     * which has persisted, and create a new report for the other court order.
     */
    private function hybridToDual(
        CourtOrderPair $courtOrderPair,
        CourtOrderRelationshipChange $courtOrderChange
    ): ReportTransitionResult {
        $result = new ReportTransitionResult();

        // where we have a hybrid transitioning to a dual, we expect the old sibling to exist as part of the hybrid
        // pair, and to be associated with the same report as the persisting court order; if the sibling is null,
        // the hybrid we're transitioning from is already invalid
        $oldSiblingId = $courtOrderChange->oldSiblingId;
        try {
            $oldSibling = $this->getOldSibling($courtOrderPair, $oldSiblingId);
        } catch (\DomainException $exception) {
            $result->errorMessages[] = 'Hybrid -> Dual: ' . $exception->getMessage();
            return $result;
        }

        // work out which order's report is persisting and will remain associated with its current (hybrid) report;
        // the other court order will get a new report
        ['persistingReportCourtOrder' => $persistingCourtOrder, 'newReportCourtOrder' => $newReportCourtOrder] =
            $this->hybridToDualAssignCourtOrders($courtOrderPair, $courtOrderChange);

        // convert existing hybrid report into single on the persisting court order
        $persistingReport = $persistingCourtOrder->getLatestReport();
        if ($persistingReport == null) {
            $result->errorMessages[] = 'Hybrid -> Dual: Could not find existing hybrid report to persist';
            return $result;
        }

        $persistingReport->setCourtOrder($persistingCourtOrder);
        $persistingCourtOrder->addReport($persistingReport);
        $persistingReport->setType("{$persistingCourtOrder->getDesiredReportType()}");

        // remove the persisting report from the sibling
        $oldSibling->removeReport($persistingReport);
        $result->updatedCourtOrders[] = $oldSibling;

        // create a new report on the court order which is the other half of the dual
        $newReport = $this->reportService->createReportFromOrder($newReportCourtOrder);
        $newReportCourtOrder->addReport($newReport);

        $result->transitioned = true;
        $result->updatedCourtOrders += [$persistingCourtOrder, $newReportCourtOrder];
        $result->updatedReports += [$persistingReport, $newReport];
        $result->messages[] = "Hybrid -> Dual: Converted hybrid report {$persistingReport->getId()} " .
            "to dual reports {$persistingReport->getId()} and {$newReport->getId()}";

        return $result;
    }

    /**
     * Dual -> Hybrid
     */
    private function dualToHybrid(
        CourtOrderPair $courtOrderPair,
        CourtOrderRelationshipChange $courtOrderChange
    ): ReportTransitionResult {
        $result = new ReportTransitionResult();

        // if this originated from a dual, there must be an old sibling
        $oldSiblingId = $courtOrderChange->oldSiblingId;
        try {
            $oldSibling = $this->getOldSibling($courtOrderPair, $oldSiblingId);
        } catch (\DomainException $exception) {
            $result->errorMessages[] = 'Dual -> Hybrid: ' . $exception->getMessage();
            return $result;
        }

        // figure out which report will persist (to become the hybrid report) and which becomes defunct
        ['persistingReport' => $persistingReport, 'defunctReport' => $defunctReport] =
            $this->dualToHybridAssignReports($courtOrderPair, $courtOrderChange, $oldSibling);
        if ($persistingReport === null || $defunctReport === null) {
            $result->errorMessages[] = 'Dual -> Hybrid: Persisting and/or defunct report unavailable';
            return $result;
        }

        $oldSibling->removeReport($persistingReport);
        $result->updatedCourtOrders[] = $oldSibling;

        /**
         * @var array<CourtOrder> $currentCourtOrders
         */
        $currentCourtOrders = [$courtOrderPair->pfaCourtOrder, $courtOrderPair->hwCourtOrder];

        // remove the defunct report from both of the current court orders, and add the persisting report to both
        // (if not already present)
        foreach ($currentCourtOrders as $courtOrder) {
            $courtOrder->removeReport($defunctReport);
            $courtOrder->addReport($persistingReport);

            $result->updatedCourtOrders[] = $courtOrder;
        }

        $result->transitioned = true;
        $result->updatedReports[] = $persistingReport;
        $result->removedReports[] = $defunctReport;
        $result->messages[] = "Dual -> Hybrid: Merged defunct report {$defunctReport->getId()} " .
            "into hybrid report {$persistingReport->getId()}";

        return $result;
    }

    /**
     * Single -> Dual
     *
     * Unlike the other transitions, this may start from an HW report (the PFA might not exist yet).
     * However, we assume that the transition to a Dual means that the court order for the other half of the dual
     * at least already exists.
     */
    private function singleToDual(CourtOrderPair $courtOrderPair): ReportTransitionResult
    {
        $result = new ReportTransitionResult();

        /** @var array<CourtOrder> $affectedCourtOrders */
        $affectedCourtOrders = [$courtOrderPair->pfaCourtOrder, $courtOrderPair->hwCourtOrder];

        // check whether the pair already share a report, and remove it from the hw court order if so
        // (duals should not have a shared report); pfa always keeps the report if both have the same report
        $hwCourtOrder = $courtOrderPair->hwCourtOrder;
        $pfaCourtOrder = $courtOrderPair->pfaCourtOrder;
        $sharedReport = $courtOrderPair->getSharedLatestReport();

        if ($sharedReport === null) {
            // no shared report, so keep the report on the previously-single court order
            // and make a new report for the other court order in the pair
            $courtOrderNeedingReport = $hwCourtOrder;
            $existingReport = $pfaCourtOrder->getLatestReport();

            if ($existingReport === null) {
                $courtOrderNeedingReport = $pfaCourtOrder;
                $existingReport = $hwCourtOrder->getLatestReport();
            }

            $courtOrderKeepingReport = ($courtOrderNeedingReport === $hwCourtOrder ? $pfaCourtOrder : $hwCourtOrder);
            $result->messages[] = "Single -> Dual: Keeping report {$existingReport->getId()} " .
                "on {$courtOrderKeepingReport->getOrderType()->value} court order " .
                "{$courtOrderKeepingReport->getCourtOrderUid()}";
        } else {
            // shared (hybrid) report, so make it the report on the pfa and remove it from the hw
            $sharedReport->setCourtOrder($pfaCourtOrder);
            $sharedReport->setType("{$pfaCourtOrder->getDesiredReportType()}");

            $hwCourtOrder->removeReport($sharedReport);

            $result->updatedReports[] = $sharedReport;

            $result->messages[] = "Single -> Dual: Removed report {$sharedReport->getId()} " .
                "from HW court order {$hwCourtOrder->getCourtOrderUid()} and set as report on PFA court order " .
                "{$pfaCourtOrder->getCourtOrderUid()}";

            $courtOrderNeedingReport = $hwCourtOrder;
            $existingReport = $sharedReport;
        }

        $newReport = $this->reportService->createReportFromOrder($courtOrderNeedingReport);
        $courtOrderNeedingReport->addReport($newReport);

        $result->updatedReports[] = $newReport;

        $result->messages[] = fn () => "Single -> Dual: Added new {$newReport->getType()} report " .
            "{$newReport->getId()} to {$courtOrderNeedingReport->getOrderType()->value} " .
            "court order {$courtOrderNeedingReport->getCourtOrderUid()}";

        if ($existingReport !== null) {
            $result->transitioned = true;
            $result->updatedCourtOrders = $affectedCourtOrders;
        } else {
            $courtOrderUids = array_map(
                fn (CourtOrder $courtOrder) => $courtOrder->getCourtOrderUid(),
                $affectedCourtOrders
            );

            $result->errorMessages[] = 'Single -> Dual: Unable to add report to other half of dual; ' .
                'UIDs of court orders involved: ' .
                implode(', ', $courtOrderUids);
        }

        return $result;
    }

    /**
     * @throws \DomainException
     */
    private function getOldSibling(CourtOrderPair $courtOrderPair, ?int $oldSiblingId): CourtOrder
    {
        // as this is a hybrid transitioning to a dual, the old sibling (part of the hybrid pair) should exist
        if ($oldSiblingId === null) {
            throw new \DomainException('Expected old sibling ID to be present for hybrid to dual transition');
        }

        // sibling hasn't changed: old sibling is the same as the current sibling
        if ($oldSiblingId === $courtOrderPair->siblingCourtOrder->getId()) {
            return $courtOrderPair->siblingCourtOrder;
        }

        // sibling has changed, so get the old one
        $oldSibling = $this->courtOrderRepository->find($oldSiblingId);

        if ($oldSibling === null) {
            throw new \DomainException('No old sibling found as part of source court order pair');
        }

        return $oldSibling;
    }

    /**
     * @return array{persistingReport: ?Report, defunctReport: ?Report}
     */
    private function dualToHybridAssignReports(
        CourtOrderPair $courtOrderPair,
        CourtOrderRelationshipChange $courtOrderChange,
        CourtOrder $oldSibling
    ): array {
        if ($courtOrderChange->hasSiblingIdChange()) {
            /*
             * When a dual case becomes hybrid through a new court order being added, the report from the persisting
             * order should be maintained and the report from the old sibling becomes defunct
             */

            // different court order became a sibling during the transition
            $persistingReport = $courtOrderPair->mainCourtOrder->getLatestReport();
            $reportType = $courtOrderPair->mainCourtOrder->getDesiredReportType();
            $defunctReport = $oldSibling->getLatestReport();
        } else {
            /*
             * When a dual case becomes hybrid through a change to the deputies, the PFA report should be maintained
             * and the HW report removed; both court orders will be associated with the persisting report
             */

            // both court orders exist already; the dual is becoming a hybrid due to deputy changes;
            // the hw report becomes defunct
            $persistingReport = $courtOrderPair->pfaCourtOrder->getLatestReport();
            $reportType = $courtOrderPair->pfaCourtOrder->getDesiredReportType();
            $defunctReport = $courtOrderPair->hwCourtOrder->getLatestReport();
        }

        $persistingReport?->setType("$reportType");

        return ['persistingReport' => $persistingReport, 'defunctReport' => $defunctReport];
    }

    /**
     * @param CourtOrderPair $courtOrderPair The court orders which are part of the dual; at least one of them was
     * part of the old pair; the one persisting provides the persisting report, while the other gets
     * a new report (potentially populated from the persisting report)
     *
     * @return array{persistingReportCourtOrder: CourtOrder, newReportCourtOrder: CourtOrder}
     *     persistingReportCourtOrder = one of the court orders in the pair whose report will be retained
     *     otherCourtOrder = the other court order in the pair whose report will be merged/deleted
     */
    private function hybridToDualAssignCourtOrders(
        CourtOrderPair $courtOrderPair,
        CourtOrderRelationshipChange $courtOrderChange
    ): array {
        /*
         * When a case moves from hybrid to dual and only one of the court orders persists while the other has been
         * replaced by a new order, the hybrid should be derived from the report for the persisting order
         * and the other court order should have a new report
         */
        if ($courtOrderChange->hasSiblingIdChange()) {
            return [
                'persistingReportCourtOrder' => $courtOrderPair->mainCourtOrder,
                'newReportCourtOrder' => $courtOrderPair->siblingCourtOrder
            ];
        }

        /*
         * When a case moves from hybrid to dual and both of the court orders persist (configuration of deputies
         * creates the change) the hybrid should be changed to the PFA report and the HW should be given a
         * new report
         */
        return [
            'persistingReportCourtOrder' => $courtOrderPair->pfaCourtOrder,
            'newReportCourtOrder' => $courtOrderPair->hwCourtOrder
        ];
    }
}
