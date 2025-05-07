<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\StagingSelectedCandidate;
use Doctrine\ORM\Mapping\Entity;

/**
 * Convert a group of candidates (with the same order UID) to a set of court order entities and relationships
 * between them.
 */
class DeputyshipCandidateConverter
{
    /**
     * @param array<StagingSelectedCandidate> $candidatesGroup
     *
     * @return array<Entity>
     */
    public function createEntitiesFromCandidates(array $candidatesGroup): array
    {
        // order in which to create/update entries:
        // 1. Insert court_order entry
        // 2. Update court_order status
        // 2. Insert court_order_deputy entries
        // 3. Update court_order_deputy statuses
        // 4. Insert court_order_report entries
        // 5. Insert court_order_ndr entries

        return [];
    }
}
