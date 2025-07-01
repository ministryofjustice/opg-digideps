<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

/**
 * The outcome of building entities from a group of deputyship candidates.
 */
enum DeputyshipBuilderResultOutcome: string
{
    // note that there may be errors but at least one entity was created successfully
    case CandidatesApplied = 'entities created successfully from candidates';

    // happens if there are no candidates, or candidates have no valid court order UID
    case Skipped = 'no candidates or no court order UID';

    // happens if a list of candidates have mismatched court order UIDs
    case CandidateListError = 'invalid candidate list - more than one court order UID';

    // happens if there is no court order insert candidate and no existing court order could be found
    case NoExistingOrder = 'no insert order candidate or no existing court order with UID could be found';

    // happens if an insert order candidate could not be applied
    case InsertOrderFailed = 'unable to apply insert order candidate';
}
