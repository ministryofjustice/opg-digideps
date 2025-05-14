<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\CourtOrder;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\StagingSelectedCandidate;
use App\Repository\CourtOrderRepository;
use App\Repository\DeputyRepository;
use App\Repository\NdrRepository;
use App\Repository\ReportRepository;
use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilderResult;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

/**
 * Convert a group of candidates (with the same order UID) to a set of court order entities and relationships
 * between them.
 */
class DeputyshipCandidateConverter
{
    public function __construct(
        private readonly CourtOrderRepository $courtOrderRepository,
        private readonly DeputyRepository $deputyRepository,
        private readonly ReportRepository $reportRepository,
        private readonly NdrRepository $ndrRepository,
    ) {
    }

    /**
     * @param array<StagingSelectedCandidate> $candidatesGroup All candidates in the list have a matching court order UID
     */
    public function createEntitiesFromCandidates(array $candidatesGroup): DeputyshipBuilderResult
    {
        // check all court order UIDs match
        $uniqueUids = array_unique(array_map(fn ($candidate) => $candidate->orderUid, $candidatesGroup));

        if (count($uniqueUids) > 1) {
            return new DeputyshipBuilderResult(
                ['cannot create entities: invalid candidate group - more than one order UID is referenced']
            );
        }

        $courtOrderUid = end($uniqueUids);
        $entities = [];
        $courtOrder = null;

        // group candidates by action into array ['<action>' => [<candidate>, <candidate>, ...], ...]
        /** @var array<string, array<StagingSelectedCandidate>> $candidatesSorted */
        $candidatesSorted = [];

        foreach ($candidatesGroup as $candidate) {
            $key = $candidate->action->value;
            if (!array_key_exists($key, $candidatesSorted)) {
                $candidatesSorted[$key] = [];
            }
            $candidatesSorted[$key][] = $candidate;
        }

        // insert court order
        $key = DeputyshipCandidateAction::InsertOrder->value;
        $insertCourtOrders = $candidatesSorted[$key] ?? [];

        if (count($insertCourtOrders) > 0) {
            // if we have more than one insert court order for this UID, we just use the last one
            // (this might happen if we have a new court order with co-deputies, so it gets 2+ rows in the
            // deputyships CSV)
            $insertCourtOrder = end($insertCourtOrders);

            // validation (of sorts)
            $missingValue = empty($insertCourtOrder->orderType)
                || empty($insertCourtOrder->status)
                || empty($insertCourtOrder->orderMadeDate);

            if ($missingValue) {
                // we couldn't create the court order, so no point continuing
                return new DeputyshipBuilderResult(
                    ["$key candidate with ID $insertCourtOrder->id missing required data - court order could not be created"]
                );
            }

            $courtOrder = new CourtOrder();
            $courtOrder->setCourtOrderUid($insertCourtOrder->orderUid);
            $courtOrder->setOrderType($insertCourtOrder->orderType ?? '');
            $courtOrder->setStatus($insertCourtOrder->status ?? '');
            $courtOrder->setOrderMadeDate(new \DateTime($insertCourtOrder->orderMadeDate ?? ''));
        }

        // if no insert court order candidate, assume we need to look up the court order instead
        if (is_null($courtOrder)) {
            $courtOrder = $this->courtOrderRepository->findOneBy(['courtOrderUid' => $courtOrderUid]);
        }

        // if we still have no court order, there's no point continuing, as we won't be able to associate
        // other records with it
        if (is_null($courtOrder)) {
            return new DeputyshipBuilderResult(
                ["$key candidate referred to non-existent court order with UID $courtOrderUid"]
            );
        }

        $errors = [];

        // update court order status
        $key = DeputyshipCandidateAction::UpdateOrderStatus->value;
        $updateCourtOrders = $candidatesSorted[$key] ?? [];
        if (count($updateCourtOrders) > 0) {
            // use the last status from the candidates
            $status = $updateCourtOrders[0]->status;
            if (!is_null($status)) {
                $courtOrder->setStatus($status);
            }
        }

        // insert court order deputy entries
        $key = DeputyshipCandidateAction::InsertOrderDeputy->value;
        $insertOrderDeputies = $candidatesSorted[$key] ?? [];
        foreach ($insertOrderDeputies as $insertOrderDeputy) {
            // get the deputy
            $deputy = $this->deputyRepository->find($insertOrderDeputy->deputyId);

            if (is_null($deputy)) {
                $errors[] = "$key candidate referred to non-existent deputy with ID $insertOrderDeputy->deputyId";
            } else {
                // associate the deputy with the court order
                $deputy->associateWithCourtOrder($courtOrder, true === $insertOrderDeputy->deputyStatusOnOrder);
                $entities[] = $deputy;
            }
        }

        // update court order deputy statuses
        $key = DeputyshipCandidateAction::UpdateDeputyStatus->value;
        $updateOrderDeputyStatuses = $candidatesSorted[$key] ?? [];

        $courtOrderDeputyRelationships = $courtOrder->getDeputyRelationships();

        foreach ($updateOrderDeputyStatuses as $updateOrderDeputyStatus) {
            // find the relationship
            $found = false;

            foreach ($courtOrderDeputyRelationships as $courtOrderDeputyRelationship) {
                if ($courtOrderDeputyRelationship->getDeputy()->getDeputyUid() === $updateOrderDeputyStatus->deputyUid) {
                    $found = true;

                    // update its status
                    $courtOrderDeputyRelationship->setIsActive(true === $updateOrderDeputyStatus->deputyStatusOnOrder);
                    $entities[] = $courtOrderDeputyRelationship;

                    break;
                }
            }

            if (!$found) {
                $errors[] = "$key candidate could not be applied - court order (UID = $updateOrderDeputyStatus->orderUid) ".
                    "to deputy (UID = $updateOrderDeputyStatus->deputyUid) relationship does not exist";
            }
        }

        // insert court order report relationships
        $key = DeputyshipCandidateAction::InsertOrderReport->value;
        $insertOrderReports = $candidatesSorted[$key] ?? [];
        foreach ($insertOrderReports as $insertOrderReport) {
            // fetch the report
            /** @var ?Report $report */
            $report = $this->reportRepository->find($insertOrderReport->reportId);

            if (is_null($report)) {
                $errors[] = "$key candidate referred to non-existent report with ID $insertOrderReport->reportId";
            } else {
                // add it to the court order
                $courtOrder->addReport($report);
            }
        }

        // insert court order ndr relationships
        $key = DeputyshipCandidateAction::InsertOrderNdr->value;
        $insertOrderNdrs = $candidatesSorted[$key] ?? [];
        foreach ($insertOrderNdrs as $insertOrderNdr) {
            // fetch the NDR
            /** @var ?Ndr $ndr */
            $ndr = $this->ndrRepository->find($insertOrderNdr->ndrId);

            if (is_null($ndr)) {
                $errors[] = "$key candidate referred to non-existent NDR with ID $insertOrderNdr->ndrId";
            } else {
                // associate it with the court order
                $courtOrder->setNdr($ndr);
            }
        }

        // ensure that the court order is the first entity saved
        $entities = array_merge([$courtOrder], $entities);

        return new DeputyshipBuilderResult($errors, $entities);
    }
}
