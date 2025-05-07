<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;

/**
 * Convert entity candidates into entities, but without saving them to the database.
 */
class DeputyshipBuilder
{
    /**
     * @param iterable<StagingSelectedCandidate> $candidates Assumption is that these are sorted by court order UID,
     *                                                       so we can group them by that even though we are processing
     *                                                       them one at a time.
     *
     * Once we have a group, we create the entities (in the correct order) and yield them to the caller as a group.
     *
     * @return iterable<DeputyshipBuilderResult>
     */
    public function build(iterable $candidates): iterable
    {
        /** @var StagingSelectedCandidate $candidate */
        foreach ($candidates as $candidate) {
            yield new DeputyshipBuilderResult();
        }
    }
}
