<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\DTO\DeputyshipRowDto;
use App\v2\Registration\Enum\DeputyshipProcessingStatus;

class DeputyshipPipelineState
{
    public DeputyshipRowDto $deputyShipRowDto;
    public DeputyshipProcessingStatus $status;

    public function __construct(
        DeputyshipRowDto $deputyShipRowDto,
        DeputyshipProcessingStatus $status = DeputyshipProcessingStatus::NOT_STARTED,
    ) {
        $this->deputyShipRowDto = $deputyShipRowDto;
        $this->status = $status;
    }
}
