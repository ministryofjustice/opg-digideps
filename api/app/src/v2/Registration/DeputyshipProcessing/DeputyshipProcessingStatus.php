<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

enum DeputyshipProcessingStatus
{
    case NOT_STARTED;
    case SKIPPED;
    case FAILED;
    case SUCCEEDED;
}
