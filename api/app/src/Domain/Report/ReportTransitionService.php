<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Report;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderPair;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Service\ReportService;

use function PHPUnit\Framework\assertNotNull;

final readonly class ReportTransitionService
{
    public function __construct(
        private ReportService $reportService
    ) {
    }

    /**
     * NB this creates the entities but doesn't persist them
     *
     * @returns ?ReportTransitionResult returns null if the transition was not attempted (report types already match)
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

        if (
            $oldReportTypeOrderKind === CourtOrderKind::Hybrid &&
            $updatedReportTypeOrderKind === CourtOrderKind::Dual
        ) {
            $result = $this->hybridToDual($report);
        }

        if (
            $oldReportTypeOrderKind === CourtOrderKind::Dual &&
            $updatedReportTypeOrderKind === CourtOrderKind::Hybrid
        ) {
            $result = $this->dualToHybrid($report, $newReportType);
        }

        if (
            $oldReportTypeOrderKind === CourtOrderKind::Single &&
            $updatedReportTypeOrderKind === CourtOrderKind::Dual
        ) {
            $result = $this->singleToDual($report);
        }

        if (
            $oldReportTypeOrderKind === CourtOrderKind::Dual &&
            $updatedReportTypeOrderKind === CourtOrderKind::Single
        ) {
            $result = $this->dualToSingle($report);
        }

        if ($result === null) {
            // this was a simple change to the report's type with no other effects
            $result = new ReportTransitionResult(
                messages: ["Report {$report->getId()} transitioned from $oldReportType to {$report->getType()}"],
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
     *
     * This only works if the hw court order is active.
     *
     * We assume that $report has its type set to the newly-derived type
     * before it is passed to this function.
     */
    private function hybridToDual(Report $report): ReportTransitionResult
    {
        $result = new ReportTransitionResult();

        // we only want active court orders, as we don't want to attach
        // the new report to an inactive hw court order
        /** @var array<CourtOrder> $courtOrders */
        $courtOrders = $report->getActiveCourtOrders();

        $courtOrderPair = CourtOrderPair::create($courtOrders);
        $pfaCourtOrder = $courtOrderPair->pfaCourtOrder;
        $hwCourtOrder = $courtOrderPair->hwCourtOrder;

        if (!$courtOrderPair->isValid()) {
            $result->errorMessages = [$courtOrderPair->invalidReason ?? 'Unknown reason'];
            return $result;
        }

        assertNotNull($pfaCourtOrder);
        assertNotNull($hwCourtOrder);

        // both court orders must have the same report we're splitting as their most-recent (hybrid) report
        $reportId = $report->getId();
        if (
            $pfaCourtOrder->getLatestReport()?->getId() !== $reportId ||
            $hwCourtOrder->getLatestReport()?->getId() !== $reportId
        ) {
            $result->errorMessages[] = "Report $reportId is not the latest report for both court orders " .
                $hwCourtOrder->getCourtOrderUid() . ' and ' . $pfaCourtOrder->getCourtOrderUid();
            return $result;
        }

        $newReport = $this->reportService->createReportFromOrder($hwCourtOrder);

        // TODO populate $newReport from existing hybrid $report?

        // TODO clean hw fields out of existing pfa $report?

        $hwCourtOrder->removeReport($report);
        $hwCourtOrder->addReport($newReport);

        $hwCourtOrder->setOrderReportType($hwCourtOrder->getDesiredReportType()->courtOrderReportType);
        $pfaCourtOrder->setOrderReportType($pfaCourtOrder->getDesiredReportType()->courtOrderReportType);

        $result->transitioned = true;
        $result->updatedCourtOrders = [$hwCourtOrder, $pfaCourtOrder];
        $result->updatedReports = [$report, $newReport];
        $result->messages = ["Converted hybrid report $reportId to dual reports $reportId and {$newReport->getId()}"];

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
     */
    private function dualToHybrid(Report $report, ReportType $reportType): ReportTransitionResult
    {
        $result = new ReportTransitionResult();

        // get active court orders on the report's client
        $firstCourtOrder = $report->getCourtOrders()->first() ?: null;
        $client = $firstCourtOrder?->getClient();
        if ($client === null) {
            $result->errorMessages = ["Could not find client for report {$report->getId()}"];
            return $result;
        }

        // if there aren't exactly two active orders, we can't construct a hybrid report
        $clientActiveCourtOrders = $client->getCourtOrders()->filter(
            fn (CourtOrder $courtOrder) => $courtOrder->getStatus() === 'ACTIVE'
        );

        $courtOrderPair = CourtOrderPair::create($clientActiveCourtOrders);
        if (!$courtOrderPair->isValid()) {
            $result->errorMessages = [$courtOrderPair->invalidReason ?? 'Unknown reason'];
            return $result;
        }

        $hwCourtOrder = $courtOrderPair->hwCourtOrder;
        $pfaCourtOrder = $courtOrderPair->pfaCourtOrder;
        assertNotNull($hwCourtOrder);
        assertNotNull($pfaCourtOrder);

        // we're going to keep the pfa report, and get rid of the hw one
        $changedReportFound = false;
        $reportId = $report->getId();
        foreach ([$hwCourtOrder, $pfaCourtOrder] as $courtOrder) {
            // we need both orders to have a latest report
            $latestReport = $courtOrder->getLatestReport();

            if ($latestReport === null) {
                $result->errorMessages[] = 'Could not find latest report for court order ' . $courtOrder->getCourtOrderUid();
            }

            // track whether one of the two latest reports on these court orders is the one transitioning;
            // if not, we don't want to apply this change now
            if ($latestReport?->getId() === $reportId) {
                $changedReportFound = true;
            }
        }

        if (!$changedReportFound) {
            $result->errorMessages[] = "Changed report is not the latest report on either of the client's linked court orders";
        }

        if ($result->hasError()) {
            return $result;
        }

        $hybridReport = $pfaCourtOrder->getLatestReport();
        $defunctReport = $hwCourtOrder->getLatestReport();
        assertNotNull($hybridReport);
        assertNotNull($defunctReport);

        // merge the hw report into the pfa report to make the hybrid report;
        // remove the hw report from the hw court order; attach the hw court order to the hybrid report

        // TODO copy data from $defunctReport into $hybridReport?

        // TODO delete $defunctReport altogether?

        $hwCourtOrder->removeReport($defunctReport);
        $hwCourtOrder->addReport($hybridReport);

        $pfaCourtOrder->setOrderReportType($reportType->courtOrderReportType);
        $hwCourtOrder->setOrderReportType($reportType->courtOrderReportType);

        $hybridReport->setType("$reportType");

        $result->transitioned = true;
        $result->updatedCourtOrders = [$pfaCourtOrder, $hwCourtOrder];
        $result->updatedReports = [$hybridReport];
        $result->removedReports = [$defunctReport];
        $result->messages = ["Merged report {$defunctReport->getId()} into hybrid report {$hybridReport->getId()}"];

        return $result;
    }

    private function singleToDual(Report $report): ReportTransitionResult
    {
        $result = new ReportTransitionResult();
        return $result;
    }

    private function dualToSingle(Report $report): ReportTransitionResult
    {
        $result = new ReportTransitionResult();
        return $result;
    }
}
