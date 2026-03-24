<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType;

use App\v2\Registration\DeputyshipProcessing\BuilderResult;
use App\v2\Registration\Enum\DeputyshipCandidatePostAction;
use App\v2\Registration\Enum\ReportTypeBuilderResultOutcome;

class ReportTypeBuilderResult extends BuilderResult
{
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
    }

    public function addActionResult(\UnitEnum $actionResult): void
    {
        if ($actionResult instanceof DeputyshipCandidatePostAction) {
            ++$this->candidatesApplied[$actionResult->value];
        }
    }
}
