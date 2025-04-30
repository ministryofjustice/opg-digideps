<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use Psr\Log\LoggerInterface;

class DeputyshipBuilder
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function build(StagingSelectedCandidate $candidate): DeputyshipPipelineState
    {
        $this->logger->info("Processing candidate with action {$candidate->action}");

        return new DeputyshipPipelineState();
    }
}
