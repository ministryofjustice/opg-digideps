<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\Report;

use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipChange;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder\CourtOrderRelationshipResult;

final readonly class ReportReassembler
{
    public function reassembleReport(CourtOrderRelationshipChange $change): CourtOrderRelationshipResult
    {
        //TODO IN A FUTURE TICKET - FOR NOW JUST PASS THROUGH THE CHANGE AS RESULT
        //We could update the report type here. We could also split or merge reports here.
        return new CourtOrderRelationshipResult($change);
    }
}
