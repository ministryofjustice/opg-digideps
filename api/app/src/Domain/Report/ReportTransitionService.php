<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Report;

use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Service\ReportService;

final readonly class ReportTransitionService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReportService $reportService
    ) {
    }

    /**
     * NB this persists any affected entities but does not flush them to the db
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

        $this->em->persist($report);

        if ($result === null) {
            $result = new ReportTransitionResult(messages: ["Report transitioned successfully"], transitioned: true);
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

        $error = $this->verifyPfaAndHwCourtOrders($courtOrders);
        if ($error !== null) {
            $result->errorMessages[] = $error;
            return $result;
        }

        $reportId = $report->getId();
        $keyedCourtOrders = [];
        foreach ($courtOrders as $courtOrder) {
            // both court orders must have the same report we're splitting as their most-recent (hybrid) report
            if ($courtOrder->getLatestReport()?->getId() !== $reportId) {
                $result->errorMessages[] = "Report $reportId is not the latest report for court order " . $courtOrder->getCourtOrderUid();
            }

            $keyedCourtOrders[$courtOrder->getOrderType()->value] = $courtOrder;
        }

        if ($result->hasError()) {
            return $result;
        }

        $pfaCourtOrder = $keyedCourtOrders[CourtOrderType::PFA->value];
        $hwCourtOrder = $keyedCourtOrders[CourtOrderType::HW->value];

        $newReport = $this->reportService->createReportFromOrder($hwCourtOrder);
        $this->em->persist($newReport);

        // TODO populate $newReport from existing hybrid $report?

        // TODO clean hw fields out of existing pfa $report?

        $hwCourtOrder->removeReport($report);
        $hwCourtOrder->addReport($newReport);

        $hwCourtOrder->setOrderReportType($hwCourtOrder->getDesiredReportType()->courtOrderReportType);
        $pfaCourtOrder->setOrderReportType($pfaCourtOrder->getDesiredReportType()->courtOrderReportType);

        $this->em->persist($hwCourtOrder);
        $this->em->persist($pfaCourtOrder);

        $result->transitioned = true;
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
        $firstCourtOrder = $report->getCourtOrders()->first();
        $client = $firstCourtOrder?->getClient();
        if ($client === null) {
            $result->errorMessages[] = "Could not find client for report {$report->getId()}";
            return $result;
        }

        // if there aren't exactly two active orders, we can't construct a hybrid report
        $clientActiveCourtOrders = $client->getCourtOrders()->filter(
            fn (CourtOrder $courtOrder) => $courtOrder->getStatus() === 'ACTIVE'
        );

        $error = $this->verifyPfaAndHwCourtOrders($clientActiveCourtOrders);
        if ($error !== null) {
            $result->errorMessages[] = $error;
            return $result;
        }

        // we're going to keep the pfa report, and get rid of the hw one
        $hwCourtOrder = null;
        $pfaCourtOrder = null;
        $hybridReport = null;
        $defunctReport = null;
        $changedReportFound = false;
        $reportId = $report->getId();
        foreach ($clientActiveCourtOrders as $courtOrder) {
            $latestReport = $courtOrder->getLatestReport();

            if ($latestReport === null) {
                $result->errorMessages[] = 'Could not find latest report for court order ' . $courtOrder->getCourtOrderUid();
            } elseif ($courtOrder->getOrderType() === CourtOrderType::PFA) {
                $pfaCourtOrder = $courtOrder;
                $hybridReport = $latestReport;
            } elseif ($courtOrder->getOrderType() === CourtOrderType::HW) {
                $hwCourtOrder = $courtOrder;
                $defunctReport = $latestReport;
            }

            // track whether one of the two reports we are looking at is the one transitioning
            if ($latestReport?->getId() === $reportId) {
                $changedReportFound = true;
            }
        }

        if (!$changedReportFound) {
            $result->errorMessages[] = "Changed report is not the latest report on either of the linked court orders";
        }

        if ($result->hasError()) {
            return $result;
        }

        // merge the hw report into the pfa report to make the hybrid report;
        // remove the hw report from the hw court order; attach the hw court order to the hybrid report

        // TODO copy data from $defunctReport into $hybridReport?

        // TODO delete $defunctReport altogether?

        $defunctReportId = $defunctReport->getId();
        $hybridReportId = $hybridReport->getId();

        $hwCourtOrder->removeReport($defunctReport);
        $hwCourtOrder->addReport($hybridReport);

        $pfaCourtOrder->setOrderReportType($reportType->courtOrderReportType);
        $hwCourtOrder->setOrderReportType($reportType->courtOrderReportType);
        $hybridReport->setType("$reportType");

        $this->em->persist($hybridReport);
        $this->em->persist($pfaCourtOrder);
        $this->em->persist($hwCourtOrder);

        $result->transitioned = true;
        $result->messages = ["Merged report $defunctReportId into hybrid report $hybridReportId"];

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

    /**
     * Check that $courtOrders contains two court orders, one HW and one PFA;
     * NB these court orders don't have to be active at this point
     *
     * @param iterable<CourtOrder> $courtOrders
     * @returns null if there were no issues, or error message if court orders could not be verified
     */
    private function verifyPfaAndHwCourtOrders(iterable $courtOrders): ?string
    {
        /** @var array<string> $courtOrderTypes */
        $courtOrderTypes = array_map(
            fn (CourtOrder $courtOrder) => $courtOrder->getOrderType()->value,
            iterator_to_array($courtOrders)
        );

        $numCourtOrderTypes = count($courtOrderTypes);
        if ($numCourtOrderTypes !== 2) {
            return "Incorrect number of court orders: expected 2, but found $numCourtOrderTypes";
        }

        $expected = [CourtOrderType::HW->value, CourtOrderType::PFA->value];

        $sorter = fn (string $a, string $b) => $a <=> $b;
        uasort($courtOrderTypes, $sorter);
        uasort($expected, $sorter);

        if ($courtOrderTypes !== $expected) {
            return 'Invalid pair of court orders: expected HW + PFA, but types were ' . implode(', ', $courtOrderTypes);
        }

        return null;
    }
}
