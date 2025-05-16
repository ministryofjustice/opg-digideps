<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\v2\Registration\DeputyshipProcessing\DeputyshipBuilderResult;
use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesGroup;
use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;

/**
 * Convert a group of candidates (with the same order UID) to a set of court order entities and relationships
 * between them.
 */
class DeputyshipCandidatesConverter
{
    public function createEntitiesFromCandidates(DeputyshipCandidatesGroup $candidatesGroup): DeputyshipBuilderResult
    {
        // TODO actually build entities and relationships using $candidatesGroup
        return new DeputyshipBuilderResult(
            outcome: DeputyshipBuilderResultOutcome::EntitiesBuiltSuccessfully,
            message: 'Builder: processed '.$candidatesGroup->totalCandidates().' candidates'
        );
    }
}
