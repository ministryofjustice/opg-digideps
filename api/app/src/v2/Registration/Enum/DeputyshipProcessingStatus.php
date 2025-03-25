<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

enum DeputyshipProcessingStatus
{
    case STARTED;
    case SKIPPED;
    case FAILED;
    case SUCCEEDED;
}
