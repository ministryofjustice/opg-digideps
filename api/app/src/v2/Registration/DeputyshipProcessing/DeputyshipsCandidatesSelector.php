<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Entity\Report\Report;
use App\Entity\StagingSelectedCandidates;
use Doctrine\ORM\EntityManagerInterface;

class DeputyshipsCandidatesSelector
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function select(): void
    {
        $selectionCandidates = [];

        $csvDeputyships[] = $this->em->createQuery(
            "SELECT sd FROM App\Entity\StagingDeputyship sd ORDER BY sd.orderUid"
        )->getResult();

        $knownCourtOrders[] = $this->em->getRepository(CourtOrder::class)
            ->createQueryBuilder('co')
            ->select('co.courtOrderUid', 'co.id', 'co.status')
            ->getQuery()
            ->getResult();

        $lookupKnownCourtOrdersStatus = array_column($knownCourtOrders, 'status', 'courtOrderUid');
        $lookupKnownCourtOrdersId = array_column($knownCourtOrders, 'id', 'courtOrderUid');

        // Not implemented - check against dd user table for being active
        $knownDeputies[] = $this->em->createQuery(
            "SELECT d.id,d.deputyUid FROM App\Entity\Deputy d"
        )->getResult();

        $lookupKnownDeputyIds = array_column($knownDeputies, 'id', 'deputyUid');

        foreach ($csvDeputyships as $csvDeputyship) {
            $courtOrderFound = array_key_exists($csvDeputyship->orderUid, $lookupKnownCourtOrdersStatus);
            $courtOrderId = $lookupKnownCourtOrdersId[$csvDeputyship->orderUid] ?? 0;

            if ($courtOrderFound && $csvDeputyship->orderStatus !== $lookupKnownCourtOrdersStatus[$csvDeputyship->orderUid]) {
                $changes = new StagingSelectedCandidates();
                $changes->action = 'UPDATE ORDER STATUS';
                $changes->orderId = $courtOrderId;
                $changes->status = $csvDeputyship->orderStatus;
                $selectionCandidates[] = $changes;
            }

            $deputyFound = array_key_exists($csvDeputyship->deputyUid, $lookupKnownDeputyIds);

            if (!$deputyFound) {
                continue;
            }

            $deputyId = $lookupKnownDeputyIds[$csvDeputyship->deputyUid];
            $csvDeputyOnCourtOrderStatus = 'ACTIVE' == $csvDeputyship->deputyStatusOnOrder;

            if ($courtOrderFound && 'ACTIVE' == $csvDeputyship->orderStatus) {
                $deputyOnCourtOrder = $this->checkDeputyOnCourtOrder($courtOrderId, $deputyId);

                $changes = new StagingSelectedCandidates();
                if ($deputyOnCourtOrder && $deputyOnCourtOrder[0]->isActive() !== $csvDeputyOnCourtOrderStatus) {
                    $changes->action = 'UPDATE DEPUTY STATUS ON ORDER';
                    $changes->orderId = $courtOrderId;
                    $changes->deputyId = $deputyId;
                    $changes->deputyStatusOnOrder = $csvDeputyOnCourtOrderStatus;
                } else {
                    $changes->action = 'INSERT ORDER DEPUTY';
                    $changes->orderId = $courtOrderId;
                    $changes->deputyId = $deputyId;
                    $changes->deputyStatusOnOrder = $csvDeputyOnCourtOrderStatus;
                }
                $selectionCandidates[] = $changes;
            }

            $client = $this->getClient($csvDeputyship->caseNumber);
            $reportTypeIsCompatible = false;
            $reportId = null;

            if (!$courtOrderFound && 'ACTIVE' == $csvDeputyship->orderStatus && !is_null($client)) {
                if (!is_null($client->getCurrentReport())) {
                    $reportTypeIsCompatible = $this->checkReportTypeIsCompatible(
                        $client->getCurrentReport()->getId(),
                        $csvDeputyship->reportType,
                        $csvDeputyship->isHybrid
                    );
                    $reportId = $client->getCurrentReport()->getId();
                }
                if ($reportTypeIsCompatible) {
                    $changes = new StagingSelectedCandidates();
                    $changes->action = 'INSERT ORDER';
                    $changes->orderUid = $csvDeputyship->orderUid;
                    $changes->orderType = $csvDeputyship->orderType;
                    $changes->status = $csvDeputyship->orderStatus;
                    $changes->clientId = $client->getId();
                    $changes->orderMadeDate = $csvDeputyship->orderMadeDate;
                    $selectionCandidates[] = $changes;

                    $changes = new StagingSelectedCandidates();
                    $changes->action = 'INSERT ORDER DEPUTY';
                    $changes->orderUid = $csvDeputyship->orderUid;
                    $changes->deputyId = $deputyId;
                    $changes->deputyStatusOnOrder = $csvDeputyOnCourtOrderStatus;
                    $selectionCandidates[] = $changes;

                    // Need all reports for client (compatibility later???)
                    $changes = new StagingSelectedCandidates();
                    $changes->action = 'INSERT ORDER REPORT';
                    $changes->orderUid = $csvDeputyship->orderUid;
                    // How to bring back previous reports
                    $changes->reportId = $reportId; // need more than one
                    $selectionCandidates[] = $changes;
                } else {
                    // if report not compatible DO WE DO ANYTHING IN THIS SITUATION
                    file_put_contents(
                        'php://stderr',
                        ' OUTPUT ---> '.print_r(
                            $csvDeputyship->orderUid.' **** REPORT NOT COMPATIBLE **** ',
                            true
                        )
                    );
                }
            } else {
                // WHAT TO DO IF ORDER STATUS IS NOT ACTIVE ANYMORE!!!!! ARE THERE ANY CLEANUP ACTIONS TO TAKE e.g. SET DEPUTY TO INACTIVE IN COURT ORDER DEPUTY TABLE?
                file_put_contents(
                    'php://stderr',
                    ' OUTPUT ---> '.print_r($csvDeputyship->orderUid.' **** ORDER NOT ACTIVE **** ', true)
                );
            }
        }
        file_put_contents('php://stderr', ' OUTPUT ---> '.print_r($selectionCandidates, true));
    }

    private function getClient(string $caseNumber): ?Client
    {
        $client = $this->em->getRepository(Client::class)->findByCaseNumber($caseNumber);

        if (is_null($client) || !is_null($client->getArchivedAt()) || !is_null($client->getDeletedAt())) {
            return null;
        }

        return $client;
    }

    private function checkReportTypeIsCompatible(int $reportId, string $csvReportType, string $isHybrid): bool
    {
        $report = $this->em->getRepository(Report::class)->findOneBy(['id' => $reportId]);
        $reportType = !is_null($report) ? $report->getType() : '';
        $adjustedCsvReportType = substr($csvReportType, 3);

        if ($isHybrid && ('102-4' == $reportType || '103-4' == $reportType) && ($reportType == $adjustedCsvReportType || '104' == $adjustedCsvReportType)) {
            return true;
        }

        return $reportType == $adjustedCsvReportType;

        //        If incoming row OrderType is IsHybrid and existing report type is 102-4 or 103-4 => compatible
        //
        //        Else if incoming row OrderType is pfa and existing report type is 102 or 103 => compatible // going to use report type
        //
        //        Else if incoming row OrderType is hw and existing report type is 104 => compatible // going to use report type
    }

    private function checkDeputyOnCourtOrder(int $courtOrderId, int $deputyId): array
    {
        return $this->em->getRepository(CourtOrderDeputy::class)->findBy(['courtOrder' => $courtOrderId, 'deputy' => $deputyId]);
    }
}
