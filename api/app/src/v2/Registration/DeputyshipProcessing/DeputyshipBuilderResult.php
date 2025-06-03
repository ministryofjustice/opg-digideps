<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Model\DeputyshipProcessingRawDbAccessResult;
use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

/**
 * Records the entities created and any errors when building from candidates for a single order.
 * This is the result of building entities for one court order, its associations to deputies and reports,
 * and updates to the order status and deputy status on order (if applicable).
 */
class DeputyshipBuilderResult
{
    private int $numCandidatesApplied = 0;

    private int $numCandidatesFailed = 0;

    public function __construct(
        private readonly DeputyshipBuilderResultOutcome $outcome,

        /** @var string[] $errors */
        private array $errors = [],

        /** @var array<string, int> $candidatesApplied */
        private array $candidatesApplied = [],
    ) {
        // initialise counts of candidates applied
        foreach (DeputyshipCandidateAction::cases() as $case) {
            $this->candidatesApplied[$case->value] = 0;
        }
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getOutcome(): DeputyshipBuilderResultOutcome
    {
        return $this->outcome;
    }

    public function getMessage(): string
    {
        $message = 'Builder result: failed candidates = '.$this->numCandidatesFailed.
            '; applied candidates = '.$this->numCandidatesApplied;

        $candidateDetails = [];
        foreach ($this->candidatesApplied as $action => $num) {
            $candidateDetails[] = $action.':'.$num;
        }

        if (count($candidateDetails) > 0) {
            $message .= '; candidate details = '.implode('|', $candidateDetails);
        }

        if (count($this->errors) > 0) {
            $message .= '; ERRORS: '.implode(' / ', $this->errors);
        }

        return $message;
    }

    public function getNumCandidatesFailed(): int
    {
        return $this->numCandidatesFailed;
    }

    public function getNumCandidatesApplied(): int
    {
        return $this->numCandidatesApplied;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Record the result of applying a candidate to the database.
     * If $result->success is true, the insert or update was a success; otherwise, it failed, and exception message is stored.
     */
    public function addCandidateResult(DeputyshipProcessingRawDbAccessResult $result): void
    {
        if ($result->success) {
            ++$this->numCandidatesApplied;
            ++$this->candidatesApplied[$result->action->value];
        } else {
            ++$this->numCandidatesFailed;

            if (!is_null($result->error)) {
                $this->errors[] = $result->error;
            }
        }
    }
}
