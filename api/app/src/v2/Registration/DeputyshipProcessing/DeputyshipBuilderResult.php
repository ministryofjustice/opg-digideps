<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use Doctrine\ORM\Mapping\Entity;

class DeputyshipBuilderResult
{
    public function __construct(
        /** @var iterable<StagingSelectedCandidate> $candidates */
        private readonly iterable $candidates,

        /** @var array<Entity> $entities */
        private readonly array $entities = [],
    ) {
    }

    /**
     * Get the number of entities which were built.
     */
    public function getNoEntitiesBuilt(): int
    {
        return count($this->entities);
    }

    /**
     * @return Entity[]
     */
    public function getEntitiesBuilt(): array
    {
        return $this->entities;
    }
}
