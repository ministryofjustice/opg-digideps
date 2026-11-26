<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing;

readonly class DeputyshipCandidatesSelectorResult
{
    public function __construct(
        /** @var \Traversable<array<string, mixed>> */
        public \Traversable $candidates,
        public int          $numCandidates,
        public ?\Exception  $exception = null,
    ) {
    }

    public function success(): bool
    {
        return is_null($this->exception);
    }
}
