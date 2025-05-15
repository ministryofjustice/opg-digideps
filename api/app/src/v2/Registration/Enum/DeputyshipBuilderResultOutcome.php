<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

/**
 * The outcome of building entities from a group of deputyship candidates.
 */
enum DeputyshipBuilderResultOutcome: string
{
    // note that there may be errors but at least one entity was created successfully
    case EntitiesBuiltSuccessfully = 'entities created successfully from candidates';

    // happens if there are no candidates, or candidates have no valid court order UID
    case Skipped = 'no candidates or no court order UID';
}
