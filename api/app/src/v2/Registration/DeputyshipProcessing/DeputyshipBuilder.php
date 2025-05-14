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

    /**
     * @param \Traversable<StagingSelectedCandidate> $candidates Assumption is that these are sorted by court order UID,
     *                                                           so we can group them by UID even though we are processing
     *                                                           one row at a time.
     *
     * Once we have a group, we create the entities (in the correct order) and yield them to the caller as a group.
     *
     * @return \Traversable<DeputyshipBuilderResult>
     */
    public function build(\Traversable $candidates): \Traversable
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
            } elseif (count($candidatesGroup) > 0) {
                // create entities from current group
                yield $this->converter->createEntitiesFromCandidates($candidatesGroup);

                // reset and start new group
                $candidatesGroup = [];
                $currentOrderUid = null;
            }
        }

        // create entities for any stragglers
        yield $this->converter->createEntitiesFromCandidates($candidatesGroup);
    }
}
