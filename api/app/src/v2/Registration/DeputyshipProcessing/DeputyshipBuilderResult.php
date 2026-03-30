<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

/**
 * Records the entities created and any errors when building from candidates for a single order.
 * This is the result of building entities for one court order, its associations to deputies and reports,
 * and updates to the order status and deputy status on order (if applicable).
 */
class DeputyshipBuilderResult extends BuilderResult
{
    private bool $isInitialised = false;

    public function __construct(
        protected \UnitEnum $outcome,
        /** @var string[] $errors */
        protected array $errors = [],
        /** @var array<string, int> $candidatesApplied */
        protected array $candidatesApplied = []
    ) {
        if (!$outcome instanceof DeputyshipBuilderResultOutcome) {
            throw new \TypeError('Incorrect enum type provided. Must provide DeputyshipBuilderResultOutcome');
        }

        parent::__construct($outcome, $errors, $candidatesApplied);
        // initialise counts of candidates applied
        foreach (DeputyshipCandidateAction::cases() as $case) {
            $this->candidatesApplied[$case->value] = 0;
        }

        $this->isInitialised = true;
    }

    public function getActionCount(\UnitEnum $outcome): int
    {
        if (!$outcome instanceof DeputyshipCandidateAction) {
            throw new \TypeError('Incorrect enum type provided. Must provide DeputyshipBuilderResultOutcome');
        }

        return $this->candidatesApplied[$outcome->value] ?? 0;
    }

    public function changeOutcome(\UnitEnum $outcome): self
    {
        if (!$this->isInitialised) {
            throw new \RuntimeException($this::class . ' is not initialised, unable to change outcome');
        }

        if (!$outcome instanceof DeputyshipBuilderResultOutcome) {
            throw new \TypeError('Incorrect enum type provided. Must provide DeputyshipBuilderResultOutcome');
        }

        $this->outcome = $outcome;

        return $this;
    }
}
