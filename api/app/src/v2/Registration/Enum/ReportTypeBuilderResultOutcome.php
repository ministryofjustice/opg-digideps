<?php

declare(strict_types=1);

namespace App\v2\Registration\Enum;

enum ReportTypeBuilderResultOutcome: string
{
    case Skipped = 'skipped';

    case NoUpdateRequired = 'noUpdate';

    case UpdateSuccess = 'updateSuccess';
}
