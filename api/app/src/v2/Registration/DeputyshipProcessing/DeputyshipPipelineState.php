<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\Enum\DeputyshipProcessingStatus;

/**
 * Record the processing state for an individual deputyship (corresponding to a single candidate derived from the CSV file).
 */
class DeputyshipPipelineState
{
    public function __construct(
        public DeputyshipProcessingStatus $status = DeputyshipProcessingStatus::STARTED,
    ) {
    }
}
