<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use App\v2\Service\DeputyshipCandidatesConverter;

/**
 * Convert entity candidates into entities, but without saving them to the database.
 */
class DeputyshipBuilder
{
    public function __construct(
        private readonly DeputyshipCandidatesConverter $converter,
    ) {
    }

    private function processCandidates(?string $orderUid, array $candidatesList, bool $dryRun): DeputyshipBuilderResult
    {
        if (is_null($orderUid)) {
            return new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::Skipped);
        }

        $candidatesGroup = DeputyshipCandidatesGroup::create($orderUid, $candidatesList);

        if (is_null($candidatesGroup)) {
            return new DeputyshipBuilderResult(DeputyshipBuilderResultOutcome::CandidateListError);
        }

        return $this->converter->convert($candidatesGroup, $dryRun);
    }

    /**
     * @param \Traversable<array<string, mixed>> $candidates Assumption is that these are sorted by court order UID,
     *                                                       so we can group them by UID even though we are processing
     *                                                       one row at a time.
     *
     * Once we have a group, we create the entities (in the correct order) and yield the result to the caller.
     *
     * @return \Traversable<DeputyshipBuilderResult>
     */
    public function build(\Traversable $candidates, bool $dryRun = false): \Traversable
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
                yield $this->processCandidates($currentOrderUid, $candidatesList, $dryRun);

                // reset and start new group
                $candidatesList = [$candidate];
                $currentOrderUid = $orderUid;
            }
        }

        // create entities for any stragglers
        yield $this->processCandidates($currentOrderUid, $candidatesList, $dryRun);
    }
}
