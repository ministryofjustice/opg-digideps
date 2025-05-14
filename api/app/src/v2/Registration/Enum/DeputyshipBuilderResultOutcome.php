<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

/**
 * The outcome of building entities from a group of deputyship candidates.
 */
enum DeputyshipBuilderResultOutcome: string
{
    case InvalidCandidateGroup = 'invalid candidate group - more than one court order UID';
    case InsufficientCourtOrderData = 'insufficient data to construct court order';
    case CourtOrderNotAvailable = 'court order could not be found or created';

    // note that there may be errors but at least one entity was created successfully
    case EntitiesBuiltSuccessfully = 'entities created successfully from candidates';
}
