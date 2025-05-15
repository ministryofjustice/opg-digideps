<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;

/**
 * Records the entities created and any errors when building from candidates for a single order.
 * This is the result of building entities for one court order, its associations to deputies and reports,
 * and updates to the order status and deputy status on order (if applicable).
 */
class DeputyshipBuilderResult
{
    public function __construct(
        private readonly DeputyshipBuilderResultOutcome $outcome,

        /** @var string[] $errors */
        private readonly array $errors = [],
    ) {
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getOutcome(): DeputyshipBuilderResultOutcome
    {
        return $this->outcome;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
