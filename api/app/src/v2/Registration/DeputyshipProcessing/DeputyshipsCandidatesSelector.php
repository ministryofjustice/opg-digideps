<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\CourtOrder;
use App\Entity\StagingDeputyship;
use App\Factory\StagingSelectedCandidateFactory;
use App\Repository\ClientRepository;
use App\Repository\CourtOrderDeputyRepository;
use App\Repository\DeputyRepository;
use App\Repository\StagingDeputyshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class DeputyshipsCandidatesSelector
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DeputyRepository $deputyRepository,
        private readonly ClientRepository $clientRepository,
        private readonly CourtOrderDeputyRepository $courtOrderDeputyRepository,
        private readonly StagingDeputyshipRepository $stagingDeputyshipRepository,
        private readonly StagingSelectedCandidateFactory $selectedCandidateFactory,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function select(): array
    {
        // delete records from candidate table ready for new candidates
        $this->em->beginTransaction();
        $this->em->createQuery('DELETE FROM App\Entity\StagingSelectedCandidates sc')->execute();
        $this->em->flush();
        $this->em->commit();

        $selectionCandidates = [];

        $csvDeputyships = $this->stagingDeputyshipRepository->findAll();

        /** @var CourtOrder[] $knownCourtOrders */
        $knownCourtOrders = $this->em->getRepository(CourtOrder::class)
            ->createQueryBuilder('co')
            ->select('co.courtOrderUid', 'co.id', 'co.status')
            ->getQuery()
            ->getResult();

        $lookupKnownCourtOrdersStatus = array_column($knownCourtOrders, 'status', 'courtOrderUid');
        $lookupKnownCourtOrdersId = array_column($knownCourtOrders, 'id', 'courtOrderUid');

        // Not implemented - check against dd user table for being active
        $knownDeputies = $this->deputyRepository->findAll();
        $lookupKnownDeputyIds = array_column($knownDeputies, 'id', 'deputyUid');

        /** @var StagingDeputyship $csvDeputyship */
        foreach ($csvDeputyships as $csvDeputyship) {
            $courtOrderFound = array_key_exists($csvDeputyship->orderUid, $lookupKnownCourtOrdersStatus);
            $courtOrderId = $lookupKnownCourtOrdersId[$csvDeputyship->orderUid] ?? 0;

            if ($courtOrderFound && $csvDeputyship->orderStatus !== $lookupKnownCourtOrdersStatus[$csvDeputyship->orderUid]) {
                $selectionCandidates[] = $this->selectedCandidateFactory->createUpdateOrderStatusCandidate(
                    $csvDeputyship,
                    $courtOrderId
                );
            }

            $deputyFound = array_key_exists($csvDeputyship->deputyUid, $lookupKnownDeputyIds);
            if (!$deputyFound) {
                continue;
            }

            $deputyId = $lookupKnownDeputyIds[$csvDeputyship->deputyUid];
            $csvDeputyOnCourtOrderStatus = 'ACTIVE' == $csvDeputyship->deputyStatusOnOrder;

            if ($courtOrderFound && 'ACTIVE' == $csvDeputyship->orderStatus) {
                $deputyOnCourtOrder = $this->courtOrderDeputyRepository->getDeputyOnCourtOrder($courtOrderId, $deputyId);

                if (is_null($deputyOnCourtOrder)) {
                    $selectionCandidates[] = $this->selectedCandidateFactory->createInsertOrderDeputyCandidate(
                        $csvDeputyship,
                        $deputyId,
                        $csvDeputyOnCourtOrderStatus
                    );
                } elseif ($deputyOnCourtOrder->isActive() !== $csvDeputyOnCourtOrderStatus) {
                    $selectionCandidates[] = $this->selectedCandidateFactory->createUpdateDeputyStatusCandidate(
                        $csvDeputyship,
                        $deputyId,
                        $courtOrderId,
                        $csvDeputyOnCourtOrderStatus
                    );
                }
            }

            // returns all clients for case number, including those where archived_at or deleted_at are null
            if (is_null($csvDeputyship->caseNumber)) {
                continue;
            }
            $client = $this->clientRepository->findByCaseNumber($csvDeputyship->caseNumber);

            if (!$courtOrderFound && ('ACTIVE' == $csvDeputyship->orderStatus) && !is_null($client)) {
                $selectionCandidates[] = $this->selectedCandidateFactory->createInsertOrderCandidate(
                    $csvDeputyship,
                    $client->getId()
                );

                $selectionCandidates[] = $this->selectedCandidateFactory->createInsertOrderDeputyCandidate(
                    $csvDeputyship,
                    $deputyId,
                    $csvDeputyOnCourtOrderStatus
                );
            } else {
                // / TODO inactive order
                error_log('INACTIVE ORDER');
            }
        }

        foreach ($selectionCandidates as $candidate) {
            $this->em->persist($candidate);
        }

        $this->em->flush();

        return $selectionCandidates;
    }
}
