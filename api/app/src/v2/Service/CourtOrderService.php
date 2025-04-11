<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\CourtOrder;
use App\Entity\User;
use App\Repository\CourtOrderRepository;
use Psr\Log\LoggerInterface;

class CourtOrderService
{
    public function __construct(
        private readonly CourtOrderRepository $courtOrderRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Get a court order by UID $uid, but only if $user is a deputy on it.
     */
    public function getByUidAsUser(string $uid, ?User $user): ?CourtOrder
    {
        if (is_null($user)) {
            return null;
        }

        $deputyUid = $user->getDeputyUid();

        if (is_null($deputyUid)) {
            $this->logger->error("Access denied to court order {$uid} as user with ID {$user->getId()} has null deputy UID");

            return null;
        }

        /** @var CourtOrder $courtOrder */
        $courtOrder = $this->courtOrderRepository->findOneBy(['courtOrderUid' => $uid]);

        // only return court order if the logged-in user is a deputy on it
        $deputyUidStr = "$deputyUid";

        $isDeputyOnCourtOrder = false;
        foreach ($courtOrder->getActiveDeputies() as $deputy) {
            if ($deputy->getDeputyUid() === $deputyUidStr) {
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
}
