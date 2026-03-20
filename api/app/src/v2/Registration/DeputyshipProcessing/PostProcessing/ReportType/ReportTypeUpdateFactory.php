<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType;

use App\Factory\DataFactoryResult;
use App\Model\DeputyshipProcessingRawDbAccessResult;
use App\Repository\StagingSelectedCandidateRepository;
use App\v2\Registration\DeputyshipProcessing\BuilderResult;
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
        $results = new ReportTypeBuilderResult(ReportTypeBuilderResultOutcome::UpdateSuccess);

        foreach ($builderResults as $builderResult) {
            match (true) {
                $builderResult instanceof ReportTypeBuilderResultOutcome =>
                    $results->addActionResult($builderResult),
                $builderResult instanceof DeputyshipProcessingRawDbAccessResult =>
                    $results->addCandidateResult($builderResult),
            };
        }

        $dataFactoryResult = new DataFactoryResult(messages: [
            'Success' => [
                'Updated report post processing ran successfully'
            ]
        ]);

        return [$dataFactoryResult, $results];
    }
}
