<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\Factory\StagingSelectedCandidateFactory;
use App\Model\CourtOrderCache;
use App\Repository\ClientRepository;
use App\Repository\CourtOrderDeputyRepository;
use App\Repository\DeputyRepository;
use App\Repository\StagingDeputyshipRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeputyshipsCandidatesSelector
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DeputyRepository $deputyRepository,
        private readonly ClientRepository $clientRepository,
        private readonly CourtOrderDeputyRepository $courtOrderDeputyRepository,
        private readonly StagingDeputyshipRepository $stagingDeputyshipRepository,
        private readonly CourtOrderCache $courtOrderCache,
        private readonly StagingSelectedCandidateFactory $candidateFactory,
    ) {
    }

    public function select(): array
    {
        // delete records from candidate table ready for new candidates
        $this->em->beginTransaction();
        $this->em->createQuery('DELETE FROM App\Entity\StagingSelectedCandidate sc')->execute();
        $this->em->flush();
        $this->em->commit();

        // read the content of the incoming deputyships CSV from the db table
        $csvDeputyships = $this->stagingDeputyshipRepository->findAll();

        // cache ids and statuses for court orders already in the db
        $this->courtOrderCache->init();

        $deputyUidToId = $this->deputyRepository->getUidToIdMapping();

        $clientCasenumberToId = $this->clientRepository->getActiveCasenumberToIdMapping();

        $candidates = [];

        /** @var StagingDeputyship $csvDeputyship */
        foreach ($csvDeputyships as $csvDeputyship) {
            $existingCourtOrderId = $this->courtOrderCache->getIdForUid($csvDeputyship->orderUid);
            $existingDeputyId = $deputyUidToId[$csvDeputyship->deputyUid] ?? null;
            $existingClientId = $clientCasenumberToId[$csvDeputyship->caseNumber] ?? null;

            $needsNewCourtOrder = (is_null($existingCourtOrderId) && !is_null($existingClientId));

            // COURT ORDER DOESN'T EXIST BUT ACTIVE CLIENT DOES; CREATE COURT ORDER
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
                $currentOrderStatus = $this->courtOrderCache->getStatusForUid($csvDeputyship->orderUid);
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
        }

        foreach ($candidates as $candidate) {
            $this->em->persist($candidate);
        }

        $this->em->flush();

        return $candidates;
    }
}
