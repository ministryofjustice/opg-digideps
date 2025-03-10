<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingDeputyship;
use App\v2\Registration\Enum\DeputyshipProcessingStatus;

class DeputyshipPipelineState
{
    public StagingDeputyship $deputyShipRowDto;
    public DeputyshipProcessingStatus $status;

    public function __construct(
        StagingDeputyship $deputyShipRowDto,
        DeputyshipProcessingStatus $status = DeputyshipProcessingStatus::NOT_STARTED,
    ) {
        $this->deputyShipRowDto = $deputyShipRowDto;
        $this->status = $status;
    }
}
