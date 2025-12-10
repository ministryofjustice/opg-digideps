<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\User;
use App\Repository\CourtOrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CourtOrderService
{
    public function __construct(
        private readonly CourtOrderRepository $courtOrderRepository,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getCourtOrderView(string $uid, ?UserInterface $user): ?array
    {
        if (!$user) {
            return null;
        }

        $userId = $user->getId();
        $courtOrder = $this->courtOrderRepository->findCourtOrderByUID($uid, $userId);
        $courtOrderView = $courtOrder[0];
        $deputiesSqlResults = $this->courtOrderRepository->findDeputiesByUID($uid);
        $reportsSqlResults = $this->courtOrderRepository->findReportsByCourtOrderUID($uid);
        $clientSqlResults = $this->courtOrderRepository->findClientByCourtOrderUID($uid);
        $courtOrderView['active_deputies'] = [];
        $courtOrderView['client'] = $clientSqlResults[0];

        $courtOrderView['reports'] = [];

        file_put_contents('php://stderr', print_r($reportsSqlResults, true));
        foreach ($reportsSqlResults as $report) {
            // Get user details for this deputy
            $report['status']['status'] = $report['report_status_cached'];
            $courtOrderView['reports'][] = $report;
        }

        foreach ($deputiesSqlResults as $deputy) {
            // Get user details for this deputy
            $userSqlResults = $this->userRepository->findUserById($deputy['user_id']);

            // Remove user_id from deputy array
            unset($deputy['user_id']);

            // Add user details under 'user'
            $deputy['user'] = $userSqlResults[0];

            // Append to active_deputies list
            $courtOrderView['active_deputies'][] = $deputy;
        }
        // Debug output (optional)

        return $courtOrderView;
    }

    /**
     * Get a court order by UID $uid, but only if $user is a deputy on it.
     */
    public function getByUidAsUser(string $uid, ?UserInterface $user): ?CourtOrder
    {
        if (is_null($user)) {
            return null;
        }

        /** @var ?CourtOrder $courtOrder */
        $courtOrder = $this->courtOrderRepository->findOneBy(['courtOrderUid' => $uid]);

        if (is_null($courtOrder)) {
            $this->logger->error("Could not find court order with UID {$uid}");

            return null;
        }

        // fetch the deputy entity by user email
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['email' => $user->getUserIdentifier()]);

        /** @var ?Deputy $deputy */
        $deputy = $user?->getDeputy();

        if (is_null($deputy)) {
            $this->logger->error("Access denied to court order {$uid} as deputy was not found for logged-in user");

            return null;
        }

        $deputyUid = $deputy->getDeputyUid();

        // only return court order if the logged-in user is a deputy on it
        $isDeputyOnCourtOrder = false;
        /** @var Deputy $activeDeputy */
        foreach ($courtOrder->getActiveDeputies() as $activeDeputy) {
            if ($activeDeputy->getDeputyUid() === $deputyUid) {
                $isDeputyOnCourtOrder = true;
                break;
            }
        }

        if (!$isDeputyOnCourtOrder) {
            $this->logger->error(
                "Access denied to court order {$uid} for deputy with UID {$deputyUid} as they are not a deputy on order"
            );

            return null;
        }

        return $courtOrder;
    }

    /**
     * Associate deputy entity with court order entity. Entities are persisted.
     *
     * If there is already an association, this updates the status of the existing association.
     */
    public function associateCourtOrderWithDeputy(Deputy $deputy, CourtOrder $courtOrder, bool $isActive = true): void
    {
        $deputy->associateWithCourtOrder($courtOrder, $isActive);
        $this->entityManager->persist($deputy);
        $this->entityManager->flush();
    }
}
