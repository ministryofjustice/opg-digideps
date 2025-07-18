<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

class DeputyshipCandidatesSelectorResult
{
    public function __construct(
        /** @var \Traversable<array<string, mixed>> */
        public readonly \Traversable $candidates,

        public readonly int $numCandidates,
        public readonly ?\Exception $exception = null,
    ) {
    }

    public function success(): bool
    {
        return is_null($this->exception);
    }
}
