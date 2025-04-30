<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\CourtOrder;
use App\Repository\ClientRepository;
use App\Repository\CourtOrderRepository;
use App\Repository\DeputyRepository;

/**
 * Cached court order, deputy, and client data, for fast lookup of IDs and statuses by UID.
 */
class DeputyshipProcessingLookupCache
{
    private bool $isInitialised = false;

    // map from court order UIDs to court order IDs
    /** @var array<string, int> */
    public array $courtOrderUidToId = [];

    // map from court order UIDs to court order statuses
    /** @var array<string, string> */
    public array $courtOrderUidToStatus = [];

    // map from deputy UIDs to deputy IDs
    /** @var array<string, int> */
    private array $deputyUidToId;

    // map from client case numbers to client IDs
    /** @var array<string, int> */
    private array $clientCasenumberToId;

    public function __construct(
        private readonly CourtOrderRepository $courtOrderRepository,
        private readonly DeputyRepository $deputyRepository,
        private readonly ClientRepository $clientRepository,
    ) {
    }

    /**
     * Initialise caches from database.
     *
     * @return bool true if the cache has been initialised
     */
    public function init(): bool
    {
        if ($this->isInitialised) {
            return $this->isInitialised;
        }

        // COURT ORDERS
        /** @var CourtOrder[] $knownCourtOrders */
        $knownCourtOrders = $this->courtOrderRepository->createQueryBuilder('co')
            ->select('co.courtOrderUid', 'co.id', 'co.status')
            ->getQuery()
            ->getArrayResult();

        $this->courtOrderUidToId = array_column($knownCourtOrders, 'id', 'courtOrderUid');
        $this->courtOrderUidToStatus = array_column($knownCourtOrders, 'status', 'courtOrderUid');

        // DEPUTIES
        $this->deputyUidToId = $this->deputyRepository->getUidToIdMapping();

        // CLIENTS
        $this->clientCasenumberToId = $this->clientRepository->getActiveCasenumberToIdMapping();

        $this->isInitialised = true;

        return $this->isInitialised;
    }

    private function throwIfNotInitialised(): void
    {
        if (!$this->isInitialised) {
            throw new \RuntimeException($this::class.' is not initialised');
        }
    }

    public function getCourtOrderIdForUid(string $uid): ?int
    {
        $this->throwIfNotInitialised();

        return $this->courtOrderUidToId[$uid] ?? null;
    }

    public function getCourtOrderStatusForUid(string $uid): ?string
    {
        $this->throwIfNotInitialised();

        return $this->courtOrderUidToStatus[$uid] ?? null;
    }

    public function getDeputyIdForUid(string $uid): ?int
    {
        $this->throwIfNotInitialised();

        return $this->deputyUidToId[$uid] ?? null;
    }

    public function getClientIdForCasenumber(?string $casenumber): ?int
    {
        $this->throwIfNotInitialised();

        return $this->clientCasenumberToId[$casenumber] ?? null;
    }
}
