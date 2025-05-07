<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;

class DeputyshipCandidatesSelectorResult
{
    public function __construct(
        /** @var StagingSelectedCandidate[] */
        public readonly iterable $candidates,

        public readonly int $numCandidates,
        public readonly ?\Exception $exception = null,
    ) {
    }

    public function success(): bool
    {
        return is_null($this->exception);
    }
}
