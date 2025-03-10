<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipsCSVIngestResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly \DateTimeInterface $dateTimeCompleted = new \DateTimeImmutable(),
    ) {
    }
}
