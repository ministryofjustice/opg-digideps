<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\CourtOrder;
use App\Entity\Deputy;
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
     * TODO deal with these exceptions instead of throwing them.
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function select(): array
    {
        // delete records from candidate table ready for new candidates
        $this->em->beginTransaction();
        $this->em->createQuery('DELETE FROM App\Entity\StagingSelectedCandidate sc')->execute();
        $this->em->flush();
        $this->em->commit();

        $selectedCandidates = [];

        $csvDeputyships = $this->stagingDeputyshipRepository->findAll();

        /** @var CourtOrder[] $knownCourtOrders */
        $knownCourtOrders = $this->em->getRepository(CourtOrder::class)
            ->createQueryBuilder('co')
            ->select('co.courtOrderUid', 'co.id', 'co.status')
            ->getQuery()
            ->getResult();

        $lookupKnownCourtOrdersStatus = array_column($knownCourtOrders, 'status', 'courtOrderUid');
        $lookupKnownCourtOrdersId = array_column($knownCourtOrders, 'id', 'courtOrderUid');

        // TODO not implemented - check against dd_user table whether user is active - is this necessary?
        $lookupKnownDeputyIds = $this->deputyRepository->getUidToIdMapping();

        // TODO what should happen if we match court order but there's no deputy, client, or report?
        /** @var StagingDeputyship $csvDeputyship */
        foreach ($csvDeputyships as $csvDeputyship) {
            $courtOrderFound = array_key_exists($csvDeputyship->orderUid, $lookupKnownCourtOrdersStatus);
            $courtOrderId = $lookupKnownCourtOrdersId[$csvDeputyship->orderUid] ?? 0;

            if ($courtOrderFound && $csvDeputyship->orderStatus !== $lookupKnownCourtOrdersStatus[$csvDeputyship->orderUid]) {
                $selectedCandidates[] = $this->selectedCandidateFactory->createUpdateOrderStatusCandidate(
                    $csvDeputyship,
                    $courtOrderId
                );
            }

            $deputyFound = array_key_exists($csvDeputyship->deputyUid, $lookupKnownDeputyIds);
            if (!$deputyFound) {
                continue;
            }

            $deputyId = $lookupKnownDeputyIds[$csvDeputyship->deputyUid];
            $isDeputyActiveOnCsvOrder = ('ACTIVE' == $csvDeputyship->deputyStatusOnOrder);

            if ($courtOrderFound && ('ACTIVE' == $csvDeputyship->orderStatus)) {
                $deputyOnCourtOrder = $this->courtOrderDeputyRepository->getDeputyOnCourtOrder($courtOrderId, $deputyId);

                if (is_null($deputyOnCourtOrder)) {
                    $selectedCandidates[] = $this->selectedCandidateFactory->createInsertOrderDeputyCandidate(
                        $csvDeputyship,
                        $deputyId,
                        $isDeputyActiveOnCsvOrder
                    );
                } elseif ($deputyOnCourtOrder->isActive() !== $isDeputyActiveOnCsvOrder) {
                    $selectedCandidates[] = $this->selectedCandidateFactory->createUpdateDeputyStatusCandidate(
                        $csvDeputyship,
                        $deputyId,
                        $courtOrderId,
                        $isDeputyActiveOnCsvOrder
                    );
                }
            }

            // returns all clients for case number, including those where archived_at or deleted_at are null
            if (is_null($csvDeputyship->caseNumber)) {
                continue;
            }
            $client = $this->clientRepository->findByCaseNumber($csvDeputyship->caseNumber);

            if (!$courtOrderFound && ('ACTIVE' == $csvDeputyship->orderStatus) && !is_null($client)) {
                $selectedCandidates[] = $this->selectedCandidateFactory->createInsertOrderCandidate(
                    $csvDeputyship,
                    $client->getId()
                );

                $selectedCandidates[] = $this->selectedCandidateFactory->createInsertOrderDeputyCandidate(
                    $csvDeputyship,
                    $deputyId,
                    $isDeputyActiveOnCsvOrder
                );
            } else {
                // TODO inactive order - do we still update it?
                error_log('INACTIVE ORDER');
            }
        }

        foreach ($selectedCandidates as $candidate) {
            $this->em->persist($candidate);
        }

        $this->em->flush();

        return $selectedCandidates;
    }
}
