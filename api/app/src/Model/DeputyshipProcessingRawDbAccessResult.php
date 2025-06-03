<?php

declare(strict_types=1);

namespace App\Model;

use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\DBAL\Result;

class DeputyshipProcessingRawDbAccessResult
{
    public function __construct(
        public DeputyshipCandidateAction $action,

        public bool $success,

        // null if the raw db access failed, otherwise the data from the operation (may be a Doctrine\DBAL\Result
        // or int court order ID)
        public int|Result|null $data = null,

        public ?string $error = null,
    ) {
    }
}
