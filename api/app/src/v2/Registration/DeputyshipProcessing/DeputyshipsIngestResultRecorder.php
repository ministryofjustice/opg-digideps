<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipsCSVIngestResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
    ) {
    }
}

class DeputyshipsIngestResultRecorder
{
    public function recordSkippedRow(DeputyshipPipelineState $state): void
    {
    }

    public function recordFailedRow(DeputyshipPipelineState $state): void
    {
    }

    public function recordProcessedRow(DeputyshipPipelineState $state): void
    {
    }

    public function result(): DeputyshipsCSVIngestResult
    {
        return new DeputyshipsCSVIngestResult(true, '');
    }
}
