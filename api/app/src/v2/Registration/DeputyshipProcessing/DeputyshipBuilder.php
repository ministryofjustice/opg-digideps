<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\Enum\DeputyshipCandidateAction;
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

    /*
     * Group candidates by action into array
     * ['INSERT' => <candidate>, 'OTHER' = [<candidate>, ...], ...]
     *
     * where <candidate> = ['<field>' => <value>, ...] (fields from a StagingSelectedCandidate in array format)
     *
     * The 'INSERT' key contains the insert order candidate, while 'OTHER' holds a list of the other
     * candidates relating to this court order
     */
    private function processCandidates(array $candidatesList): DeputyshipBuilderResult
    {
        $candidatesGrouped = [];

        /* @var array<string, mixed> $candidate */
        foreach ($candidatesList as $candidatesListItem) {
            if (DeputyshipCandidateAction::InsertOrder === $candidatesListItem['action']) {
                // we only need to keep one insert order candidate
                $candidatesGrouped['INSERT'] = $candidatesListItem;
            } else {
                if (!array_key_exists('OTHER', $candidatesGrouped)) {
                    $candidatesGrouped['OTHER'] = [];
                }
                $candidatesGrouped['OTHER'][] = $candidatesListItem;
            }
        }

        return $this->converter->createEntitiesFromCandidates($candidatesGrouped);
    }

    /**
     * @param \Traversable<array<string, string>> $candidates Assumption is that these are sorted by court order UID,
     *                                                        so we can group them by UID even though we are processing
     *                                                        one row at a time.
     *
     * Once we have a group, we create the entities (in the correct order) and yield the result to the caller.
     *
     * @return \Traversable<DeputyshipBuilderResult>
     */
    public function build(\Traversable $candidates): \Traversable
    {
        $currentOrderUid = null;
        $candidatesList = [];

        /** @var array<string, string> $candidate */
        foreach ($candidates as $candidate) {
            $orderUid = $candidate['orderUid'];

            if (is_null($currentOrderUid)) {
                $currentOrderUid = $orderUid;
            }

            if ($currentOrderUid === $orderUid) {
                // add candidate to group
                $candidatesList[] = $candidate;
            } elseif (count($candidatesList) > 0) {
                // process group
                yield $this->processCandidates($candidatesList);

                // reset and start new group
                $candidatesList = [$candidate];
                $currentOrderUid = $orderUid;
            }
        }

        // create entities for any stragglers
        yield $this->processCandidates($candidatesList);
    }
}
