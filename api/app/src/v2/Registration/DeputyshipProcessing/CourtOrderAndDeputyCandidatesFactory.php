<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\Factory\StagingSelectedCandidateFactory;
use App\Model\DeputyshipProcessingLookupCache;
use App\Repository\CourtOrderDeputyRepository;

/**
 * Create candidate order and court order <-> deputy for a StagingDeputyship (which typically represents a row in the ingested
 * deputyships CSV file).
 */
class CourtOrderAndDeputyCandidatesFactory
{
    public function __construct(
        private readonly CourtOrderDeputyRepository $courtOrderDeputyRepository,
        private readonly DeputyshipProcessingLookupCache $deputyshipLookupCache,
        private readonly StagingSelectedCandidateFactory $candidateFactory,
    ) {
    }

    public function cacheLookupTables(): void
    {
        $this->deputyshipLookupCache->init();
    }

    /**
     * Create CourtOrder and CourtOrderDeputy candidates for a deputyship (typically ingested CSV row).
     *
     * @return StagingSelectedCandidate[]
     */
    public function create(StagingDeputyship $csvDeputyship): array
    {
        $existingCourtOrderId = $this->deputyshipLookupCache->getCourtOrderIdForUid($csvDeputyship->orderUid);
        $existingDeputyId = $this->deputyshipLookupCache->getDeputyIdForUid($csvDeputyship->deputyUid);
        $existingClientId = $this->deputyshipLookupCache->getClientIdForCasenumber($csvDeputyship->caseNumber);
        $needsNewCourtOrder = (is_null($existingCourtOrderId) && !is_null($existingClientId));

        $candidates = [];

        // COURT ORDER DOESN'T EXIST BUT ACTIVE CLIENT DOES; INSERT NEW COURT ORDER
        if ($needsNewCourtOrder) {
            $candidates[] = $this->candidateFactory->createInsertOrderCandidate(
                $csvDeputyship,
                $existingClientId
            );
        }

        // COURT ORDER DOESN'T EXIST, BUT DEPUTY DOES; INSERT NEW COURT ORDER <-> DEPUTY RELATIONSHIP
        if ($needsNewCourtOrder && !is_null($existingDeputyId)) {
            $candidates[] = $this->candidateFactory->createInsertOrderDeputyCandidate(
                $csvDeputyship,
                $existingDeputyId
            );
        }

        // COURT ORDER EXISTS; UPDATE COURT ORDER STATUS
        if (!is_null($existingCourtOrderId)) {
            // if court order status is different, update it
            $currentOrderStatus = $this->deputyshipLookupCache->getCourtOrderStatusForUid($csvDeputyship->orderUid);

            if ($csvDeputyship->orderStatus !== $currentOrderStatus) {
                $candidates[] = $this->candidateFactory->createUpdateOrderStatusCandidate(
                    $csvDeputyship,
                    $existingCourtOrderId
                );
            }
        }

        // COURT ORDER EXISTS, AS DOES DEPUTY; INSERT OR UPDATE COURT ORDER <-> DEPUTY RELATIONSHIP
        if (!is_null($existingCourtOrderId) && !is_null($existingDeputyId)) {
            $existingRelationship = $this->courtOrderDeputyRepository->getDeputyOnCourtOrder(
                $existingCourtOrderId,
                $existingDeputyId
            );

            if (is_null($existingRelationship)) {
                // no existing court order deputy, so add relationship with correct status
                $candidates[] = $this->candidateFactory->createInsertOrderDeputyCandidate(
                    $csvDeputyship,
                    $existingDeputyId
                );
            } elseif ($csvDeputyship->deputyIsActiveOnOrder() !== $existingRelationship->isActive()) {
                // existing court order deputy relationship but status is different, so update
                $candidates[] = $this->candidateFactory->createUpdateDeputyStatusCandidate(
                    $csvDeputyship,
                    $existingDeputyId,
                    $existingCourtOrderId
                );
            }
        }

        return $candidates;
    }
}
