<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\PostProcessing\ReportType;

use App\Service\ReportTypeService;

class ReportTypeCandidatesReportMatching
{
    public static function processCandidateGroup(array $candidateGroup, string $latestReportType): ?string
    {
        $reportAttributes = ReportTypeService::getReportTypeAttributes($latestReportType);

        $baseReportType = $reportAttributes['baseReport'];
        $orderType = $reportAttributes['orderType'];
        $hasNonLayDeputy = $reportAttributes['hasNonLayDeputy'];
        $deputyType = $reportAttributes['deputyType'];
        $isHybrid = $reportAttributes['isHybrid'];
        $courtOrderUid = null;

        // Track report changes while looping through candidates
        $reportChanges = [
            'baseReportChange' => false,
            'nonLayDeputyAdded' => false,
            'changedToHybrid' => false,
            'changedFromHybrid' => false,
        ];
        foreach ($candidateGroup as $candidate) {
            if (is_null($courtOrderUid)) {
                $courtOrderUid = $candidate['orderUid'];
            }

            // store base report type; 102, 103, 104..
            $baseReportTypeCandidate = substr($candidate['reportType'], 3);

            if (!$reportChanges['nonLayDeputyAdded'] && !$hasNonLayDeputy) {
                // PA & PRO can not both exist on the same courtOrder
                $reportChanges['nonLayDeputyAdded'] = match ($candidate['deputyType']) {
                    'PA', 'PRO' => true,
                    default => false,
                };

                if ($reportChanges['nonLayDeputyAdded']) {
                    $deputyType = $candidate['deputyType'];
                }
            }

            if (!$reportChanges['changedToHybrid'] && !$isHybrid && $candidate['isHybrid']) {
                $reportChanges['changedToHybrid'] = $isHybrid = $candidate['isHybrid'];

                // Currently orderType must be set as hw to correctly set hybrid report
                $orderType = 'hw';
            }

            if (!$reportChanges['baseReportChange'] && $baseReportTypeCandidate !== $baseReportType) {
                $reportChanges['baseReportChange'] = true;

                $baseReportType = $baseReportTypeCandidate;
            }

            if (!$reportChanges['changedFromHybrid'] && $isHybrid && !$candidate['isHybrid']) {
                $reportChanges['changedToHybrid'] = $isHybrid = $candidate['isHybrid'];

                $orderType = match ($baseReportType) {
                    '102', '103' => 'pfa',
                    '104' => 'hw',
                };
            }
        }

        // reportType requires updating
        if (in_array(true, $reportChanges)) {
            return ReportTypeService::determineReportType($baseReportType, $orderType, $deputyType, $isHybrid);
        }

        return null;
    }
}
