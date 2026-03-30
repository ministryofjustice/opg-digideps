<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType;

use App\Factory\DataFactoryResult;
use App\Repository\StagingSelectedCandidateRepository;
use App\v2\Registration\DeputyshipProcessing\BuilderResult;
use App\v2\Registration\Enum\DeputyshipCandidatePostAction;
use App\v2\Registration\Enum\ReportTypeBuilderResultOutcome;

class ReportTypeUpdateFactory
{
    public function __construct(
        private readonly StagingSelectedCandidateRepository $staging,
        private readonly ReportTypeBuilder $reportTypeBuilder,
    ) {
    }

    public function getName(): string
    {
        return 'ReportTypeUpdate';
    }

    /**
     * @return array<DataFactoryResult, ?BuilderResult>
     */
    public function run(): array
    {
        $candidates = $this->staging->getOrdersWithPossibleReportTypeChange();
        $builderResults = $this->reportTypeBuilder->build($candidates);
        $interimResults = new ReportTypeBuilderResult(ReportTypeBuilderResultOutcome::UpdateSuccess);

        foreach ($builderResults as $builderResult) {
            $interimResults->addCandidateResult($builderResult);
        }

        $results = match (true) {
            $interimResults->getActionCount(DeputyshipCandidatePostAction::UpdateReportTypeSkipped) > 0
                => $interimResults->changeOutcome(ReportTypeBuilderResultOutcome::Skipped),
            $interimResults->getActionCount(DeputyshipCandidatePostAction::UpdateReportTypeNoAction) > 0
                => $interimResults->changeOutcome(ReportTypeBuilderResultOutcome::NoUpdateRequired),
            default => $interimResults
        };

        $dataFactoryResult = new DataFactoryResult(messages: [
            'Success' => [
                'Updated report type post processing ran successfully'
            ]
        ]);

        return [$dataFactoryResult, $results];
    }
}
