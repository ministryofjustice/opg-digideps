<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType;

use App\Model\DeputyshipProcessingRawDbAccess;
use App\Model\DeputyshipProcessingRawDbAccessResult;
use App\Model\DeputyshipReportProcessingLookupCache;
use App\v2\Registration\Enum\ReportTypeBuilderResultOutcome;

class ReportTypeBuilder
{
    public function __construct(
        private readonly DeputyshipReportProcessingLookupCache $reportLookupCache,
        private readonly DeputyshipProcessingRawDbAccess $dbAccess,
    ) {
    }

    public function processCandidates(?string $orderUid, array $candidatesList): ReportTypeBuilderResultOutcome|DeputyshipProcessingRawDbAccessResult
    {
        if (is_null($orderUid)) {
            return ReportTypeBuilderResultOutcome::Skipped;
        }

        $latestReportId = $this->reportLookupCache->getLatestReportIdForCourtOrderUid($orderUid);
        $latestReportType = $this->reportLookupCache->getLatestReportTypeForId($latestReportId);

        $updatedReportType = ReportTypeCandidatesReportMatching::processCandidateGroup(
            $candidatesList,
            $latestReportType
        );

        if (is_null($updatedReportType)) {
            return ReportTypeBuilderResultOutcome::NoUpdateRequired;
        }

        $result = $this->dbAccess->updateReportType($latestReportId, $updatedReportType);
        $this->dbAccess->flush();

        return $result;
    }

    public function build(\Traversable $candidates): \Traversable
    {
        $this->reportLookupCache->init();

        $currentOrderUid = null;
        $candidatesList = [];

        foreach ($candidates as $candidate) {
            $orderUid = $candidate['order_uid'];

            if (is_null($currentOrderUid)) {
                $currentOrderUid = $orderUid;
            }

            if ($currentOrderUid === $orderUid) {
                // add candidate to group
                $candidatesList[] = $candidate;
            } elseif (count($candidatesList) > 0) {
                // process group
                yield $this->processCandidates($currentOrderUid, $candidatesList);

                // reset and start new group
                $candidatesList = [$candidate];
                $currentOrderUid = $orderUid;
            }
        }
    }
}
