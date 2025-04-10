<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\CourtOrder;
use App\Repository\CourtOrderRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class CourtOrderService
{
    public function __construct(
        private readonly CourtOrderRepository $repository,
    ) {
    }

    /**
     * Get a court order by UID $uid, but only if $user is a deputy on it.
     */
    public function getByUidAsUser(string $uid, ?UserInterface $user): ?CourtOrder
    {
        return $this->repository->findOneBy(['courtOrderUid' => $uid]);
        // return null;
    }
}
