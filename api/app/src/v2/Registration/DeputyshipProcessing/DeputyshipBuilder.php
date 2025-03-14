<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipBuilder
{
    public function build(DeputyshipPipelineState $state): DeputyshipPipelineState
    {
        return $state;
    }
}
