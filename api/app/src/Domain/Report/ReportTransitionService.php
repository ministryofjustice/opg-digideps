<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Report;

use Doctrine\Common\Collections\Collection;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderPair;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Service\ReportService;

final readonly class ReportTransitionService
{
    public function __construct(
        private ReportService $reportService
    ) {
    }

    /**
     * NB this creates the entities but doesn't persist them
     *
     * @return ?ReportTransitionResult returns null if the transition was not attempted (report types already match)
     */
    public function transitionReport(Report $report, ?ReportType $oldReportType, ReportType $newReportType): ?ReportTransitionResult
    {
        if ($oldReportType === $newReportType) {
            return null;
        }

        $result = null;

        $report->setType("$newReportType");

        $oldReportTypeOrderKind = $oldReportType?->courtOrderKind;
        $updatedReportTypeOrderKind = $newReportType->courtOrderKind;

        // if we are doing any transitions involving hybrids
        $isPfa = ($oldReportType?->courtOrderType === CourtOrderType::PFA);

        if (
            $isPfa &&
            $oldReportTypeOrderKind === CourtOrderKind::Hybrid &&
            $updatedReportTypeOrderKind === CourtOrderKind::Dual
        ) {
            $result = $this->hybridToDual($report, $newReportType);
        }

        if (
            $isPfa &&
            $oldReportTypeOrderKind === CourtOrderKind::Dual &&
            $updatedReportTypeOrderKind === CourtOrderKind::Hybrid
        ) {
            $result = $this->dualToHybrid($report, $newReportType);
        }

        if (
            $isPfa &&
            $oldReportTypeOrderKind === CourtOrderKind::Dual &&
            $updatedReportTypeOrderKind === CourtOrderKind::Single
        ) {
            $result = $this->dualToSingle($report, $newReportType);
        }

        // we don't check if this is a PFA report here, as we
        // may have a single HW report transitioning to a dual
        // (i.e. there may not be a PFA report yet)
        if (
            $oldReportTypeOrderKind === CourtOrderKind::Single &&
            $updatedReportTypeOrderKind === CourtOrderKind::Dual
        ) {
            $result = $this->singleToDual($report, $newReportType);
        }

        if ($result === null) {
            // this was a simple change to the report's type with no other effects or changes to court orders;
            // e.g. 102 -> 103, 102-4 -> 102, 104 -> 102-4 (single -> single, single -> hybrid, hybrid -> single)
            $result = new ReportTransitionResult(
                messages: ["Simple: Report {$report->getId()} transitioned from $oldReportType to {$report->getType()}"],
                transitioned: true,
                updatedReports: [$report]
            );
        }

        return $result;
    }

    /**
     * Hybrid -> Dual
     *
     * A hybrid report is already associated with two court orders. Break the
     * link to the hw court order, then add a new report to that hw court order.
     * The existing hybrid report remains attached to the pfa court order but is
     * converted into a non-hybrid report.
     */
    private function hybridToDual(Report $report, ReportType $reportType): ReportTransitionResult
    {
        $result = new ReportTransitionResult();

        // we only want active court orders, as we don't want to attach
        // the new report to an inactive hw court order
        $courtOrders = $report->getActiveCourtOrders();

        $courtOrderPair = CourtOrderPair::create($courtOrders);
        if (!$courtOrderPair->isValid()) {
            $result->errorMessages[] = 'Hybrid -> Dual: ' . ($courtOrderPair->invalidReason ?? 'Unknown reason');
            return $result;
        }

        $pfaCourtOrder = $courtOrderPair->pfaCourtOrder;
        $hwCourtOrder = $courtOrderPair->hwCourtOrder;
        assert($pfaCourtOrder !== null);
        assert($hwCourtOrder !== null);

        // both court orders must have the same report we're splitting as their most-recent (hybrid) report
        $reportId = $report->getId();
        if (
            $pfaCourtOrder->getLatestReport()?->getId() !== $reportId ||
            $hwCourtOrder->getLatestReport()?->getId() !== $reportId
        ) {
            $result->errorMessages[] = "Hybrid -> Dual: Report $reportId is not the latest report for both court orders " .
                $hwCourtOrder->getCourtOrderUid() . ' and ' . $pfaCourtOrder->getCourtOrderUid();
            return $result;
        }

        $report->setType("$reportType");

        $newReport = $this->reportService->createReportFromOrder($hwCourtOrder);

        // TODO populate $newReport from existing hybrid $report?

        // TODO clean hw fields out of existing pfa $report?

        $hwCourtOrder->removeReport($report);
        $hwCourtOrder->addReport($newReport);

        $hwCourtOrder->setOrderReportType($hwCourtOrder->getDesiredReportType()->courtOrderReportType);
        $pfaCourtOrder->setOrderReportType($pfaCourtOrder->getDesiredReportType()->courtOrderReportType);

        $result->transitioned = true;
        $result->updatedCourtOrders = [$hwCourtOrder, $pfaCourtOrder];
        $result->updatedReports = [$newReport, $report];
        $result->messages = [
            "Hybrid -> Dual: Converted hybrid report $reportId to dual reports $reportId and {$newReport->getId()}"
        ];

        return $result;
    }

    /**
     * Dual -> Hybrid
     *
     * We need to find the latest report for the other active
     * order for this client; if there is more than one other active
     * order, or if there is a single other court order but it lacks a latest report,
     * we can't do anything about changing from dual to hybrid
     *
     * Sever the link from the hw report to its current (hopefully hw) court order,
     * and attach that court order to the merged hybrid report instead
     *
     * We could potentially delete the hw report at this point
     *
     * @param ReportType $reportType The type for the report after it is changed to hybrid
     */
    private function dualToHybrid(Report $report, ReportType $reportType): ReportTransitionResult
    {
        $result = new ReportTransitionResult();

        $clientActiveCourtOrders = $this->getActiveClientCourtOrders($report);
        if ($clientActiveCourtOrders === null) {
            $result->errorMessages[] = "Dual -> Hybrid: Could not find client for report {$report->getId()}";
            return $result;
        }

        $courtOrderPair = CourtOrderPair::create($clientActiveCourtOrders);
        $dualError = $this->verifyDualCourtOrders($courtOrderPair, $report);
        if ($dualError !== null) {
            $result->errorMessages[] = ['Dual -> Hybrid: ' . $dualError];
            return $result;
        }

        assert($courtOrderPair->pfaCourtOrder !== null);
        assert($courtOrderPair->hwCourtOrder !== null);

        $hybridReport = $courtOrderPair->pfaCourtOrder->getLatestReport();
        $defunctReport = $courtOrderPair->hwCourtOrder->getLatestReport();
        assert($hybridReport !== null);
        assert($defunctReport !== null);

        // TODO copy data from $defunctReport into $hybridReport?

        // TODO delete $defunctReport altogether?

        // merge the hw report into the pfa report to make the hybrid report;
        // remove the hw report from the hw court order; attach the hw court order to the hybrid report
        $courtOrderPair->hwCourtOrder->removeReport($defunctReport);
        $courtOrderPair->hwCourtOrder->addReport($hybridReport);

        $courtOrderPair->hwCourtOrder->setOrderReportType($reportType->courtOrderReportType);
        $courtOrderPair->pfaCourtOrder->setOrderReportType($reportType->courtOrderReportType);

        $hybridReport->setType("$reportType");

        $result->transitioned = true;
        $result->updatedCourtOrders = [$courtOrderPair->hwCourtOrder, $courtOrderPair->pfaCourtOrder];
        $result->updatedReports = [$hybridReport];
        $result->removedReports = [$defunctReport];
        $result->messages[] = "Dual -> Hybrid: Merged report {$defunctReport->getId()} " .
            "into hybrid report {$hybridReport->getId()}";

        return $result;
    }

    /**
     * Dual -> Single
     *
     * Only keep the report on the court order associated with $report. Remove the other.
     * Note that this has to work for either the report being retained or the report being removed,
     * as we could be transitioning from HW+PFA -> HW or HW+PFA -> PFA.
     *
     * @param ReportType $reportType The new type of the report which is being retained
     */
    private function dualToSingle(Report $report, ReportType $reportType): ReportTransitionResult
    {
        $result = new ReportTransitionResult();

        // get the two court orders for the report: one is the single court order on $report;
        // the other is the court order for the second report in the dual
        $clientActiveCourtOrders = $this->getActiveClientCourtOrders($report);
        if ($clientActiveCourtOrders === null) {
            $result->errorMessages[] = "Dual -> Single: Could not find client for report {$report->getId()}";
            return $result;
        }

        $courtOrderPair = CourtOrderPair::create($clientActiveCourtOrders);
        $dualError = $this->verifyDualCourtOrders($courtOrderPair, $report);
        if ($dualError !== null) {
            $result->errorMessages[] = ['Dual -> Single: ' . $dualError];
            return $result;
        }

        assert($courtOrderPair->pfaCourtOrder !== null);
        assert($courtOrderPair->hwCourtOrder !== null);

        /** @var array<CourtOrder> $affectedCourtOrders */
        $affectedCourtOrders = [$courtOrderPair->pfaCourtOrder, $courtOrderPair->hwCourtOrder];

        // keep the report on the court order whose court order report type matches the new report type;
        // remove the other report from the second court order
        foreach ($affectedCourtOrders as $courtOrder) {
            $possiblyDefunctReport = $courtOrder->getLatestReport();
            assert($possiblyDefunctReport !== null);

            // we want to keep the report whose court order matches the new report type (PFA, HW)
            // and whose latest report is the transitioning report
            if (
                $courtOrder->getOrderType() === $reportType->courtOrderType &&
                $possiblyDefunctReport === $report
            ) {
                // the report we want to keep
                $report->setType("$reportType");
                $courtOrder->setOrderReportType($reportType->courtOrderReportType);

                $result->updatedReports[] = $possiblyDefunctReport;
            } else {
                // the defunct report
                $courtOrder->removeReport($possiblyDefunctReport);

                $result->transitioned = true;
                $result->removedReports[] = $possiblyDefunctReport;
                $result->messages[] = "Dual -> Single: Removed report {$possiblyDefunctReport->getId()} " .
                    "from court order {$courtOrder->getCourtOrderUid()}";
            }
        }

        // if no report was removed, something went wrong somewhere
        if (!$result->transitioned) {
            $result->errorMessages[] = [
                "Dual -> Single: Could not remove report {$report->getId()}: no applicable court order found"
            ];
        } else {
            $result->updatedCourtOrders = $affectedCourtOrders;
        }

        return $result;
    }

    /**
     * Single -> Dual
     *
     * Unlike the other transitions, this may start from an HW report (the PFA might not exist yet).
     *
     * @param ReportType $reportType The new type of the report which is being retained; the other half of the dual
     * will be the report for the *other* report type (e.g. if new type is PFA, a new report will be added to the HW
     * court order)
     */
    private function singleToDual(Report $report, ReportType $reportType): ReportTransitionResult
    {
        $result = new ReportTransitionResult();

        // find the court orders for this report's client and check we have both types of court order (PFA, HW)
        // and one of them has $report as its latest report
        $clientActiveCourtOrders = $this->getActiveClientCourtOrders($report);
        if ($clientActiveCourtOrders === null) {
            $result->errorMessages[] = "Single -> Dual: Could not find client for report {$report->getId()}";
            return $result;
        }

        $courtOrderPair = CourtOrderPair::create($clientActiveCourtOrders);
        if (!$courtOrderPair->isValid()) {
            $result->errorMessages[] = ['Single -> Dual: ' . ($courtOrderPair->invalidReason ?? 'Unknown reason')];
            return $result;
        }

        // check that $report only has one court order (it should be a single)
        if (count($report->getCourtOrders()) > 1) {
            $result->errorMessages[] = "Single -> Dual: Report {$report->getId()} is attached to more than one court order";
            return $result;
        }

        assert($courtOrderPair->pfaCourtOrder !== null);
        assert($courtOrderPair->hwCourtOrder !== null);

        /** @var array<CourtOrder> $affectedCourtOrders */
        $affectedCourtOrders = [$courtOrderPair->pfaCourtOrder, $courtOrderPair->hwCourtOrder];

        // create a new report on the court order whose type does not match $report's
        foreach ($affectedCourtOrders as $courtOrder) {
            if (
                $courtOrder->getOrderType() === CourtOrderType::tryFrom($report->getType()) &&
                $courtOrder->getLatestReport() === $report
            ) {
                // the court order for the existing single report
                $courtOrder->setOrderReportType($reportType->courtOrderReportType);
                $report->setType("$reportType");

                $result->updatedReports[] = $report;
            } else {
                // the other active client on this court order which doesn't have a report yet: make a new one
                $newReport = $this->reportService->createReportFromOrder($courtOrder);
                $newOrderReportType = ReportType::tryFrom($newReport->getType())?->courtOrderReportType;

                if ($newOrderReportType !== null) {
                    $courtOrder->setOrderReportType($newOrderReportType);
                }

                $courtOrder->addReport($newReport);

                $result->transitioned = true;
                $result->updatedReports[] = $newReport;
                $result->messages[] = "Single -> Dual: Added new {$newReport->getType()} report {$newReport->getId()} " .
                    "to court order {$courtOrder->getCourtOrderUid()}";
            }
        }

        if (!$result->transitioned) {
            $result->errorMessages[] = [
                "Single -> Dual: Could not add report {$report->getId()} to court order: no applicable court order found"
            ];
        } else {
            $result->updatedCourtOrders = $affectedCourtOrders;
        }

        return $result;
    }

    /**
     * @return ?Collection<int, CourtOrder> null if client cannot be found, otherwise the client's active court orders
     */
    private function getActiveClientCourtOrders(Report $report): ?Collection
    {
        // get active court orders on the report's client
        $firstCourtOrder = $report->getCourtOrders()->first() ?: null;
        $client = $firstCourtOrder?->getClient();
        return $client?->getCourtOrders()->filter(
            fn(CourtOrder $courtOrder) => $courtOrder->getStatus() === 'ACTIVE'
        );
    }

    /**
     * Check that:
     * - court order pair is valid (i.e. two active court orders, one pfa and one hw)
     * - both court orders have a latest report which is not null
     * - one of the reports on one of the court orders is $report
     *
     * @return ?string if null, there was no error; otherwise, there is a problem with the court orders and their reports
     */
    private function verifyDualCourtOrders(CourtOrderPair $courtOrderPair, Report $report): ?string
    {
        if (!$courtOrderPair->isValid()) {
            return $courtOrderPair->invalidReason ?? 'Unknown reason';
        }

        $msg = [];
        if ($courtOrderPair->pfaCourtOrder?->getLatestReport() === null) {
            $msg[] = 'PFA court order is missing latest report';
        }
        if ($courtOrderPair->hwCourtOrder?->getLatestReport() === null) {
            $msg[] = 'HW Court order is missing latest report';
        }
        if (count($msg) > 0) {
            return implode('; ', $msg);
        }

        $changedReportFound = false;
        $reportId = $report->getId();
        foreach ([$courtOrderPair->hwCourtOrder, $courtOrderPair->pfaCourtOrder] as $courtOrder) {
            if ($courtOrder === null) {
                return 'At least one of the court orders in a dual was null';
            }

            // we need both orders to have a latest report
            $latestReport = $courtOrder->getLatestReport();

            if ($latestReport === null) {
                return 'Could not find latest report for court order ' . $courtOrder->getCourtOrderUid();
            }

            // track whether one of the two latest reports on these court orders is the one transitioning;
            // if not, we don't want to apply this change now
            if ($latestReport->getId() === $reportId) {
                $changedReportFound = true;
            }
        }

        if (!$changedReportFound) {
            return "Changed report is not the latest report on either of the client's linked court orders";
        }

        return null;
    }
}
