<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use App\v2\Service\DeputyshipCandidateConverter;

/**
 * Convert entity candidates into entities, but without saving them to the database.
 */
class DeputyshipBuilder
{
    public function __construct(
        private readonly DeputyshipCandidateConverter $converter,
    ) {
    }

    private function createEntitiesForGroup(array $candidatesGroup): DeputyshipBuilderResult
    {
        if (0 === count($candidatesGroup)) {
            return new DeputyshipBuilderResult($candidatesGroup);
        }

        $entities = $this->converter->createEntitiesFromCandidates($candidatesGroup);

        return new DeputyshipBuilderResult($candidatesGroup, $entities);
    }

    /**
     * @param iterable<StagingSelectedCandidate> $candidates Assumption is that these are sorted by court order UID,
     *                                                       so we can group them by UID even though we are processing
     *                                                       one row at a time.
     *
     * Once we have a group, we create the entities (in the correct order) and yield them to the caller as a group.
     *
     * @return iterable<DeputyshipBuilderResult>
     */
    public function build(iterable $candidates): iterable
    {
        $currentOrderUid = null;
        $candidatesGroup = [];

        foreach ($candidates as $candidate) {
            if (is_null($currentOrderUid)) {
                $currentOrderUid = $candidate->orderUid;
            }

            if ($currentOrderUid === $candidate->orderUid) {
                // add candidate to group
                $candidatesGroup[] = $candidate;
            } else {
                // create entities from current group
                yield $this->createEntitiesForGroup($candidatesGroup);

                // reset and start new group
                $candidatesGroup = [];
            }
        }

        // create entities for any stragglers
        yield $this->createEntitiesForGroup($candidatesGroup);
    }
}
