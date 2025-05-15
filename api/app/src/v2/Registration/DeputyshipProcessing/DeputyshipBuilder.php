<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

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
     * @param \Traversable<mixed> $candidates Assumption is that these are sorted by court order UID,
     *                                        so we can group them by UID even though we are processing
     *                                        one row at a time.
     *
     * Once we have a group, we create the entities (in the correct order) and yield them to the caller.
     *
     * @return \Traversable<DeputyshipBuilderResult>
     */
    public function build(\Traversable $candidates): \Traversable
    {
        $currentOrderUid = null;
        $candidatesGroup = [];
        $totalCandidates = 0;

        foreach ($candidates as $candidate) {
            $orderUid = $candidate['orderUid'];

            if (is_null($currentOrderUid)) {
                $currentOrderUid = $orderUid;
            }

            if ($currentOrderUid === $orderUid) {
                // add candidate to group
                $candidatesGroup[] = $candidate;
            } elseif (count($candidatesGroup) > 0) {
                // create entities from current group
                yield $this->converter->createEntitiesFromCandidates($candidatesGroup);

                $totalCandidates += count($candidatesGroup);
                error_log(
                    '+++++++++++++ GROUP OF '.count($candidatesGroup).
                    ' CANDIDATES TO BE CONVERTED; TOTAL SO FAR = '.$totalCandidates
                );

                // reset and start new group
                $candidatesGroup = [$candidate];
                $currentOrderUid = $orderUid;
            }
        }

        // create entities for any stragglers
        yield $this->converter->createEntitiesFromCandidates($candidatesGroup);
    }
}
