<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\Report;

use App\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipChange;
use App\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipResult;

final readonly class ReportReassembler
{
    public function reassembleReport(CourtOrderRelationshipChange $change): CourtOrderRelationshipResult
    {
        //TODO IN A FUTURE TICKET - FOR NOW JUST PASS THROUGH THE CHANGE AS RESULT
        return new CourtOrderRelationshipResult($change);
    }
}
