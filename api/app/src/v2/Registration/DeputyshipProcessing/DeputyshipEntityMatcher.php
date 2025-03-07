<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipEntityMatcher
{
    public function match(DeputyshipPipelineState $state): DeputyshipPipelineState
    {
        return new DeputyshipPipelineState();
    }
}
