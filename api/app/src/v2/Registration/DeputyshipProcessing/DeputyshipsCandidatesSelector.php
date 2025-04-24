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
            $deputyIsActiveOnOrder = ('ACTIVE' === $csvDeputyship->deputyStatusOnOrder);

            if (is_null($existingCourtOrderId)) {
                // COURT ORDER DOESN'T EXIST

                $existingClientId = $clientCasenumberToId[$csvDeputyship->caseNumber] ?? null;

                // if client exists, add court order (no client => no court order)
                if (!is_null($existingClientId)) {
                    // ACTIVE CLIENT EXISTS
                    $candidates[] = $this->candidateFactory->createInsertOrderCandidate(
                        $csvDeputyship,
                        $existingClientId
                    );

                    // if a matching deputy exists, associate them with the court order we're adding;
                    // (again, no client => no court order and no court order deputy relationship)
                    if (!is_null($existingDeputyId)) {
                        // DEPUTY EXISTS
                        $candidates[] = $this->candidateFactory->createInsertOrderDeputyCandidate(
                            $csvDeputyship,
                            $existingDeputyId,
                            $deputyIsActiveOnOrder
                        );
                    }
                }
            } else {
                // COURT ORDER EXISTS

                // if court order status is different, update it
                $currentOrderStatus = $this->courtOrderCache->getStatusForUid($csvDeputyship->orderUid);
                if ($csvDeputyship->orderStatus !== $currentOrderStatus) {
                    $candidates[] = $this->candidateFactory->createUpdateOrderStatusCandidate(
                        $csvDeputyship,
                        $existingCourtOrderId
                    );
                }

                // update or add court order deputy relationship
                if (!is_null($existingDeputyId)) {
                    // DEPUTY EXISTS
                    $existingRelationship = $this->courtOrderDeputyRepository->getDeputyOnCourtOrder(
                        $existingCourtOrderId,
                        $existingDeputyId
                    );

                    if (is_null($existingRelationship)) {
                        // no existing court order deputy, so add relationship with correct status
                        $candidates[] = $this->candidateFactory->createInsertOrderDeputyCandidate(
                            $csvDeputyship,
                            $existingDeputyId,
                            $deputyIsActiveOnOrder
                        );
                    } elseif ($deputyIsActiveOnOrder !== $existingRelationship->isActive()) {
                        // existing court order deputy relationship but status is different, so update
                        $candidates[] = $this->candidateFactory->createUpdateDeputyStatusCandidate(
                            $csvDeputyship,
                            $existingDeputyId,
                            $existingCourtOrderId,
                            $deputyIsActiveOnOrder
                        );
                    }
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
