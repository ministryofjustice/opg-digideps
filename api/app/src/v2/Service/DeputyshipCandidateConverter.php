<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\StagingSelectedCandidate;
use App\Factory\CourtOrderFactory;
use App\Repository\CourtOrderRepository;
use App\Repository\DeputyRepository;
use App\Repository\ReportRepository;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\ORM\Mapping\Entity;
use Psr\Log\LoggerInterface;

/**
 * Convert a group of candidates (with the same order UID) to a set of court order entities and relationships
 * between them.
 */
class DeputyshipCandidateConverter
{
    public function __construct(
        private readonly CourtOrderFactory $courtOrderFactory,
        private readonly CourtOrderRepository $courtOrderRepository,
        private readonly DeputyRepository $deputyRepository,
        private readonly ReportRepository $reportRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<StagingSelectedCandidate> $candidatesGroup All candidates in the list have a matching court order UID
     *
     * @return array<Entity> Ordering is important: entities should be persisted in the order they appear in this array
     */
    public function createEntitiesFromCandidates(array $candidatesGroup): array
    {
        // check all court order UIDs match
        $uniqueUids = array_unique(array_map(
            function ($candidate) { return $candidate->orderUid; },
            $candidatesGroup
        ));

        if (count($uniqueUids) > 1) {
            $this->logger->error('cannot create entities: invalid candidate group - more than one order UID is referenced');

            return [];
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

            $courtOrder = $this->courtOrderFactory->create(
                $insertCourtOrder->orderUid,
                $insertCourtOrder->orderType,
                $insertCourtOrder->status,
                $insertCourtOrder->orderMadeDate
            );

            if (is_null($courtOrder)) {
                $this->logger->error(
                    "$key candidate with ID $insertCourtOrder->id missing required data - ".
                    'court order could not be created'
                );

                // we couldn't create the court order, so no point continuing
                return [];
            }
        }

        // if not insert court order candidate, assume we need to look it up instead
        if (is_null($courtOrder)) {
            $courtOrder = $this->courtOrderRepository->findOneBy(['courtOrderUid' => $courtOrderUid]);
        }

        // if we still have no court order, there's no point continuing
        if (is_null($courtOrder)) {
            $this->logger->error("$key candidate referred to non-existent court order with UID $courtOrderUid");

            return [];
        }

        // update court order status
        $key = DeputyshipCandidateAction::UpdateOrderStatus->value;
        $updateCourtOrders = $candidatesSorted[$key] ?? [];
        if (count($updateCourtOrders) > 0) {
            // use the last status from the candidates
            $courtOrder->setStatus(end($updateCourtOrders)->status);
        }

        // insert court order deputy entries
        $key = DeputyshipCandidateAction::InsertOrderDeputy->value;
        $insertOrderDeputies = $candidatesSorted[$key] ?? [];
        foreach ($insertOrderDeputies as $insertOrderDeputy) {
            // get the deputy
            $deputy = $this->deputyRepository->find($insertOrderDeputy->deputyId);

            if (is_null($deputy)) {
                $this->logger->error("$key candidate referred to non-existent deputy with UID $insertOrderDeputy->deputyUid");
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
                }
            }

            if (!$found) {
                $this->logger->error(
                    "$key candidate could not be applied - court order (UID = $updateOrderDeputyStatus->orderUid) ".
                    "to deputy (UID = $updateOrderDeputyStatus->deputyUid) relationship does not exist"
                );
            }
        }

        // insert court order report relationships
        $key = DeputyshipCandidateAction::InsertOrderReport->value;
        $insertOrderReports = $candidatesSorted[$key] ?? [];
        foreach ($insertOrderReports as $insertOrderReport) {
            // fetch the report
            $report = $this->reportRepository->find($insertOrderReport->reportId);

            if (is_null($report)) {
                $this->logger->error(
                    "$key candidate referred to non-existent report with ID $insertOrderReport->reportId"
                );
            } else {
                // add it to the court order
                $courtOrder->addReport($report);
            }
        }

        // TODO insert court order ndr relationships

        // ensure that the court order is saved first
        $entities = array_merge([$courtOrder], $entities);

        return $entities;
    }
}
