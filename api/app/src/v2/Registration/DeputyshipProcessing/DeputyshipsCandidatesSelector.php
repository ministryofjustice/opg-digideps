<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\CourtOrder;
use App\Entity\CourtOrderDeputy;
use App\Factory\StagingSelectedCandidateFactory;
use App\Repository\ClientRepository;
use App\Repository\DeputyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class DeputyshipsCandidatesSelector
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DeputyRepository $deputyRepository,
        private readonly ClientRepository $clientRepository,
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
        $knownDeputies = $this->deputyRepository->getKnownDeputies();
        $lookupKnownDeputyIds = array_column($knownDeputies, 'id', 'deputyUid');

        foreach ($csvDeputyships as $csvDeputyship) {
            $courtOrderFound = array_key_exists($csvDeputyship->orderUid, $lookupKnownCourtOrdersStatus);
            $courtOrderId = $lookupKnownCourtOrdersId[$csvDeputyship->orderUid] ?? 0;

            if ($courtOrderFound && $csvDeputyship->orderStatus !== $lookupKnownCourtOrdersStatus[$csvDeputyship->orderUid]) {
                $selectionCandidates[] = $this->selectedCandidateFactory->createUpdateOrderStatusCandidate($csvDeputyship, $courtOrderId);
            }

            $deputyFound = array_key_exists($csvDeputyship->deputyUid, $lookupKnownDeputyIds);

            if (!$deputyFound) {
                continue;
            }

            $deputyId = $lookupKnownDeputyIds[$csvDeputyship->deputyUid];
            $csvDeputyOnCourtOrderStatus = 'ACTIVE' == $csvDeputyship->deputyStatusOnOrder;

            if ($courtOrderFound && 'ACTIVE' == $csvDeputyship->orderStatus) {
                $deputyOnCourtOrder = $this->checkDeputyOnCourtOrder($courtOrderId, $deputyId);

                if ($deputyOnCourtOrder && $deputyOnCourtOrder[0]->isActive() !== $csvDeputyOnCourtOrderStatus) {
                    $changes = $this->selectedCandidateFactory->createUpdateDeputyStatusCandidate($csvDeputyship, $deputyId, $courtOrderId, $csvDeputyOnCourtOrderStatus);
                } else {
                    $changes = $this->selectedCandidateFactory->createInsertOrderDeputyCandidate($csvDeputyship, $deputyId, $csvDeputyOnCourtOrderStatus);
                }
                $selectionCandidates[] = $changes;
            }

            // does not return clients where archived_at or deleted_at properties are not null
            $client = $this->clientRepository->findByCaseNumber($csvDeputyship->caseNumber);

            if (!$courtOrderFound && 'ACTIVE' == $csvDeputyship->orderStatus && !is_null($client)) {
                $selectionCandidates[] = $this->selectedCandidateFactory->createInsertOrderCandidate($csvDeputyship, $client->getId());

                $selectionCandidates[] = $this->selectedCandidateFactory->createInsertOrderDeputyCandidate($csvDeputyship, $deputyId, $csvDeputyOnCourtOrderStatus);
            } else {
                // WHAT TO DO IF ORDER STATUS IS NOT ACTIVE ANYMORE!!!!! ARE THERE ANY CLEANUP ACTIONS TO TAKE e.g. SET DEPUTY TO INACTIVE IN COURT ORDER DEPUTY TABLE?

                file_put_contents(
                    'php://stderr',
                    ' OUTPUT ---> '.print_r($csvDeputyship->orderUid.' **** ORDER NOT ACTIVE **** ', true)
                );
            }
        }

        foreach ($selectionCandidates as $candidate) {
            $this->em->persist($candidate);
        }
        $this->em->flush();

        return $selectionCandidates;
    }

    private function checkDeputyOnCourtOrder(int $courtOrderId, int $deputyId): array
    {
        return $this->em->getRepository(CourtOrderDeputy::class)->findBy(['courtOrder' => $courtOrderId, 'deputy' => $deputyId]);
    }
}
