<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\CourtOrder;
use App\Repository\CourtOrderRepository;
use http\Exception\RuntimeException;

/**
 * Cached court order data, for fast lookup of court order IDs and statuses by UID.
 */
class CourtOrderCache
{
    private bool $isInitialised = false;

    /** @var array<string, int> */
    private array $courtOrderUidToId = [];

    /** @var array<string, string> */
    private array $courtOrderUidToStatus = [];

    public function __construct(
        private readonly CourtOrderRepository $courtOrderRepository,
    ) {
    }

    public function init(): void
    {
        /** @var CourtOrder[] $knownCourtOrders */
        $knownCourtOrders = $this->courtOrderRepository->createQueryBuilder('co')
            ->select('co.courtOrderUid', 'co.id', 'co.status')
            ->getQuery()
            ->getResult();

        $this->courtOrderUidToId = array_column($knownCourtOrders, 'id', 'courtOrderUid');
        $this->courtOrderUidToStatus = array_column($knownCourtOrders, 'status', 'courtOrderUid');

        $this->isInitialised = true;
    }

    public function getIdForUid(string $uid): ?int
    {
        if (!$this->isInitialised) {
            throw new RuntimeException('CourtOrderCache is not initialised');
        }

        return $this->courtOrderUidToId[$uid] ?? null;
    }

    public function getStatusForUid(string $uid): ?string
    {
        if (!$this->isInitialised) {
            throw new RuntimeException('CourtOrderCache is not initialised');
        }

        return $this->courtOrderUidToStatus[$uid] ?? null;
    }
}
