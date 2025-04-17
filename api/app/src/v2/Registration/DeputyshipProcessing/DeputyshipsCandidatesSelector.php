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

    public function select(): array
    {
        // delete records from candidate table ready for new candidates
        $this->em->beginTransaction();
        $this->em->createQuery('DELETE FROM App\Entity\StagingSelectedCandidates sc')->execute();
        $this->em->flush();
        $this->em->commit();

        $selectionCandidates = [];

        $csvDeputyships = $this->em->createQuery(
            "SELECT sd FROM App\Entity\StagingDeputyship sd ORDER BY sd.orderUid"
        )->getResult();

        $knownCourtOrders = $this->em->getRepository(CourtOrder::class)
            ->createQueryBuilder('co')
            ->select('co.courtOrderUid', 'co.id', 'co.status')
            ->getQuery()
            ->getResult();

        $lookupKnownCourtOrdersStatus = array_column($knownCourtOrders, 'status', 'courtOrderUid');
        $lookupKnownCourtOrdersId = array_column($knownCourtOrders, 'id', 'courtOrderUid');

        // Not implemented - check against dd user table for being active
        $knownDeputies = $this->em->createQuery(
            "SELECT d.id,d.deputyUid FROM App\Entity\Deputy d"
        )->getResult();

        $lookupKnownDeputyIds = array_column($knownDeputies, 'id', 'deputyUid');

        foreach ($csvDeputyships as $csvDeputyship) {
            $courtOrderFound = array_key_exists($csvDeputyship->orderUid, $lookupKnownCourtOrdersStatus);
            $courtOrderId = $lookupKnownCourtOrdersId[$csvDeputyship->orderUid] ?? 0;

            if ($courtOrderFound && $csvDeputyship->orderStatus !== $lookupKnownCourtOrdersStatus[$csvDeputyship->orderUid]) {
                $changes = new StagingSelectedCandidates();
                $changes->action = 'UPDATE ORDER STATUS';
                $changes->orderUid = $csvDeputyship->orderUid;
                $changes->deputyUid = $csvDeputyship->deputyUid;
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
                    $changes->orderUid = $csvDeputyship->orderUid;
                    $changes->orderId = $courtOrderId;
                    $changes->deputyId = $deputyId;
                    $changes->deputyUid = $csvDeputyship->deputyUid;
                    $changes->deputyStatusOnOrder = $csvDeputyOnCourtOrderStatus;
                } else {
                    $changes->action = 'INSERT ORDER DEPUTY';
                    $changes->orderUid = $csvDeputyship->orderUid;
                    $changes->orderId = $courtOrderId;
                    $changes->deputyId = $deputyId;
                    $changes->deputyUid = $csvDeputyship->deputyUid;
                    $changes->deputyStatusOnOrder = $csvDeputyOnCourtOrderStatus;
                }
                $selectionCandidates[] = $changes;
            }

            $client = $this->getClient($csvDeputyship->caseNumber);
            $reportTypeIsCompatible = false;
            $currentReportId = null;

            if (!$courtOrderFound && 'ACTIVE' == $csvDeputyship->orderStatus && !is_null($client)) {
                // ACTION: Need to handle clients with more than one active report
                if (!is_null($client->getCurrentReport())) {
                    $reportTypeIsCompatible = $this->checkReportTypeIsCompatible(
                        $client->getCurrentReport()->getId(),
                        $csvDeputyship->reportType,
                        $csvDeputyship->isHybrid
                    );
                    $currentReportId = $client->getCurrentReport()->getId();
                }

                $changes = new StagingSelectedCandidates();
                $changes->action = 'INSERT ORDER';
                $changes->orderUid = $csvDeputyship->orderUid;
                $changes->orderType = $csvDeputyship->orderType;
                $changes->status = $csvDeputyship->orderStatus;
                $changes->clientId = $client->getId();
                $changes->orderMadeDate = $csvDeputyship->orderMadeDate;
                $changes->deputyUid = $csvDeputyship->deputyUid;
                $selectionCandidates[] = $changes;

                $changes = new StagingSelectedCandidates();
                $changes->action = 'INSERT ORDER DEPUTY';
                $changes->orderUid = $csvDeputyship->orderUid;
                $changes->deputyId = $deputyId;
                $changes->deputyStatusOnOrder = $csvDeputyOnCourtOrderStatus;
                $changes->deputyUid = $csvDeputyship->deputyUid;
                $selectionCandidates[] = $changes;

                // store reportIds for court order report insertion
                $reportIds = [];
                $reportIds[] = $currentReportId;

                if ($reportTypeIsCompatible || 0 != $csvDeputyship->isHybrid) {
                    $clientId = $client->getId();

                    $historicReports =
                        $this->em->getRepository(Report::class)
                        ->createQueryBuilder('r')
                        ->select('r.id', 'r.type', 'c.id AS clientId') // fetches the id value from the client
                        ->innerJoin('r.client', 'c') // joins the client entity
                        ->where('c.id = :clientId')
                        ->andWhere('r.submitDate IS NOT NULL OR r.unSubmitDate IS NOT NULL')
                        ->setParameter('clientId', $clientId)
                        ->orderBy('r.id', 'DESC')
                        ->getQuery()
                        ->getArrayResult();

                    foreach ($historicReports as $report) {
                        $historicReportCount = count($historicReports);

                        // if count > 0 and is hybrid then attach all historical pfa and hw reports to same court order
                        // else if not hybrid then check order type compatibility first to decide match
                        if ($historicReportCount > 0) {
                            $orderTypeCompatibility = $this->checkOrderTypeIsCompatible($report['type'], $csvDeputyship->orderType, $csvDeputyship->isHybrid);

                            if ($orderTypeCompatibility) {
                                $reportIds[] = $report->getId();
                            } else {
                                file_put_contents('php://stderr', ' OUTPUT ---> '.print_r(
                                    $report->getId.' **** ORDER TYPE NOT COMPATIBLE **** ',
                                    true
                                )
                                );
                            }
                        }
                    }

                    // Loop through reportIds to populate row for each report (historical and current)
                    foreach ($reportIds as $reportId) {
                        // Need all reports for client (compatibility later???)
                        $changes = new StagingSelectedCandidates();
                        $changes->action = 'INSERT ORDER REPORT';
                        $changes->orderUid = $csvDeputyship->orderUid;
                        $changes->reportId = $reportId;
                        $changes->deputyUid = $csvDeputyship->deputyUid;
                    }
                    $selectionCandidates[] = $changes;
                } else {
                    // Split out to handle DUAL court orders where the type on the clients current report doesn't match
                    // the type on the dual court order row
                    $changes = new StagingSelectedCandidates();
                    $changes->action = 'DUAL ORDER FOUND';
                    $changes->orderUid = $csvDeputyship->orderUid;
                    $changes->reportId = $reportId;
                    $changes->deputyUid = $csvDeputyship->deputyUid;
                    $selectionCandidates[] = $changes;
                }
            } else {
                // WHAT TO DO IF ORDER STATUS IS NOT ACTIVE ANYMORE!!!!! ARE THERE ANY CLEANUP ACTIONS TO TAKE e.g. SET DEPUTY TO INACTIVE IN COURT ORDER DEPUTY TABLE?

                file_put_contents(
                    'php://stderr',
                    ' OUTPUT ---> '.print_r($csvDeputyship->orderUid.' **** ORDER NOT ACTIVE **** ', true)
                );
            }
        }

        foreach ($selectionCandidates as $candidate) {
            file_put_contents('php://stderr', ' OUTPUT FROM SELECTOR ---> '.print_r($candidate, true));
            $this->em->persist($candidate);
        }
        $this->em->flush();

        return $selectionCandidates;
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

        // Do the report types cover pro/pa's?
        if ('1' == $isHybrid && ('102-4' == $reportType || '103-4' == $reportType) && ($reportType == $adjustedCsvReportType || '104' == $adjustedCsvReportType)) {
            return true;
        }

        return $reportType == $adjustedCsvReportType;

        //        If incoming row OrderType is IsHybrid and existing report type is 102-4 or 103-4 => compatible
        //        Else if incoming row OrderType is pfa and existing report type is 102 or 103 => compatible // going to use report type
        //        Else if incoming row OrderType is hw and existing report type is 104 => compatible // going to use report type
    }

    private function checkDeputyOnCourtOrder(int $courtOrderId, int $deputyId): array
    {
        return $this->em->getRepository(CourtOrderDeputy::class)->findBy(['courtOrder' => $courtOrderId, 'deputy' => $deputyId]);
    }

    private function checkOrderTypeIsCompatible(string $reportType, string $csvOrderType, string $isCourtOrderHybrid): bool
    {
        $nonHybridTypes = [
            '102' => 'pfa',
            '102-5' => 'pfa',
            '102-6' => 'pfa',
            '103' => 'pfa',
            '103-5' => 'pfa',
            '103-6' => 'pfa',
            '104' => 'hw',
            '104-5' => 'hw',
            '104-6' => 'hw',
        ];

        // Need to check how pro/pa order types are represented in deputyship staging table
        $hybridTypes = ['102-4', '103-4', '102-4-5', '102-4-6', '103-4-5', '103-4-6'];

        if ('0' == $isCourtOrderHybrid) {
            $correspondingOrderType = $nonHybridTypes[$reportType];

            return $correspondingOrderType == $csvOrderType;
        } else {
            return in_array($reportType, $hybridTypes);
        }
    }
}
