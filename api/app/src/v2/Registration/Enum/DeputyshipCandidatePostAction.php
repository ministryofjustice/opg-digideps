<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

enum DeputyshipCandidatePostAction: string
{
    case UpdateReportType = 'PU_RT';
    case UpdateReportTypeSkipped = 'PU_RT_S';
    case UpdateReportTypeNoAction = 'PU_RT_N';
}
