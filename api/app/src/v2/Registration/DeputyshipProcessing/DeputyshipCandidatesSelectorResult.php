<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing;

use OPG\Digideps\Backend\Entity\Staging\StagingSelectedCandidate;

readonly class DeputyshipCandidatesSelectorResult
{
    public function __construct(
        /** @var \Traversable<StagingSelectedCandidate> */
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
