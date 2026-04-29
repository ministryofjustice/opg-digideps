<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing;

class DeputyshipsCSVIngestResult
{
    public function __construct(
        public readonly bool $success = false,
        public readonly string $message = '',
        public readonly \DateTimeInterface $dateTimeCompleted = new \DateTimeImmutable(),
    ) {
    }
}
