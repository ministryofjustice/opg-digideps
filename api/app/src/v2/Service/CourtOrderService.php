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
     * @param bool $logDuplicateError If set to true, if the relationship already exists, it is logged as an error;
     *                                otherwise it's ignored and not logged. Ignoring this is to prevent expected
     *                                log messages from triggering alarms.
     *
     * @return bool true if the association was made; false if the deputy or court order doesn't exist, or if they
     *              do and they are already associated
     */
    public function associateCourtOrderWithDeputy(
        Deputy $deputy,
        CourtOrder $courtOrder,
        bool $isActive = true,
        bool $logDuplicateError = true,
    ): bool {
        $deputyUid = $deputy->getDeputyUid();
        $courtOrderUid = $courtOrder->getCourtOrderUid();

        // check whether association already exists
        foreach ($deputy->getCourtOrdersWithStatus() as $courtOrderWithStatus) {
            /** @var CourtOrder $existingCourtOrder */
            $existingCourtOrder = $courtOrderWithStatus['courtOrder'];
            if ($existingCourtOrder->getCourtOrderUid() === $courtOrderUid) {
                if ($logDuplicateError) {
                    $this->logger->error("Deputy with UID $deputyUid is already associated with court order with UID $courtOrderUid");
                }

                return false;
            }
        }

        $deputy->associateWithCourtOrder($courtOrder, $isActive);

        $this->entityManager->persist($deputy);
        $this->entityManager->flush();

        return true;
    }
}
