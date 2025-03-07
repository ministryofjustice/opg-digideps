<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipPersister
{
    public function persist(DeputyshipPipelineState $state): DeputyshipPipelineState
    {
        return $state;
    }
}
