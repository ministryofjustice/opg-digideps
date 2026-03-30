<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType;

use App\v2\Registration\DeputyshipProcessing\BuilderResult;
use App\v2\Registration\Enum\DeputyshipBuilderResultOutcome;
use App\v2\Registration\Enum\DeputyshipCandidatePostAction;
use App\v2\Registration\Enum\ReportTypeBuilderResultOutcome;

class ReportTypeBuilderResult extends BuilderResult
{
    private bool $isInitialised = false;

    public function __construct(
        protected \UnitEnum $outcome,
        /** @var string[] $errors */
        protected array $errors = [],
        /** @var array<string, int> $candidatesApplied */
        protected array $candidatesApplied = []
    ) {
        if (!$outcome instanceof ReportTypeBuilderResultOutcome) {
            throw new \TypeError('Incorrect enum type provided. Must provide ReportTypeBuilderResultOutcome');
        }

        parent::__construct($outcome, $errors, $candidatesApplied);
        foreach (DeputyshipCandidatePostAction::cases() as $case) {
            match ($case) {
                DeputyshipCandidatePostAction::UpdateReportType,
                DeputyshipCandidatePostAction::UpdateReportTypeSkipped,
                DeputyshipCandidatePostAction::UpdateReportTypeNoAction => $this->candidatesApplied[$case->value] = 0
            };
        }

        $this->isInitialised = true;
    }

    public function getActionCount(\UnitEnum $action): ?int
    {
        if (!$action instanceof DeputyshipCandidatePostAction) {
            throw new \TypeError('Incorrect enum type provided. Must provide DeputyshipCandidatePostAction');
        }

        return match ($action) {
            DeputyshipCandidatePostAction::UpdateReportType,
            DeputyshipCandidatePostAction::UpdateReportTypeSkipped,
            DeputyshipCandidatePostAction::UpdateReportTypeNoAction => $this->candidatesApplied[$action->value] ?? 0,
            default => null
        };
    }

    public function changeOutcome(\UnitEnum $outcome): self
    {
        if (!$this->isInitialised) {
            throw new \RuntimeException($this::class . ' is not initialised, unable to change outcome');
        }

        if (!$outcome instanceof ReportTypeBuilderResultOutcome) {
            throw new \TypeError('Incorrect enum type provided. Must provide ReportTypeBuilderResultOutcome');
        }

        $this->outcome = $outcome;

        return $this;
    }
}
