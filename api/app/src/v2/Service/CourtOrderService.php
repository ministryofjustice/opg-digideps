<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\User;
use App\Repository\CourtOrderRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CourtOrderService
{
    public function __construct(
        private readonly CourtOrderRepository $courtOrderRepository,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Get a court order by UID $uid, but only if $user is a deputy on it.
     */
    public function getByUidAsUser(string $uid, ?UserInterface $user): ?CourtOrder
    {
        /** @var ?CourtOrder $courtOrder */
        $courtOrder = $this->courtOrderRepository->findOneBy(['courtOrderUid' => $uid]);

        if (is_null($courtOrder)) {
            $this->logger->error("Could not find court order with UID {$uid}");

            return null;
        }

        // check user access to court order
        if (is_null($user)) {
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
}
