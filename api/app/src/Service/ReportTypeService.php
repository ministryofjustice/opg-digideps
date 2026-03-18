<?php

declare(strict_types=1);

namespace App\Service;

class ReportTypeService
{
    public static function determineReportType(string $opgReport, string $orderType, string $deputyType, bool $isHybrid = false): string
    {
        $reportType = str_starts_with('opg', strtolower($opgReport)) ? substr($opgReport, 3) : $opgReport;

        $deputySuffix = match ($deputyType) {
            'LAY' => '',
            'PRO' => '-5',
            'PA' => '-6',
        };

        $hybridSuffix = $isHybrid ? '-4' . $deputySuffix : $deputySuffix;

        return match ([$opgReport, $orderType, $isHybrid]) {
            ['opg102','pfa', false], ['opg103','pfa', false], ['opg104','hw', false] => $reportType . $deputySuffix,
            ['opg102', 'hw', true], ['opg103', 'hw', true] => $reportType . $hybridSuffix,
        };
    }

    public static function getReportTypeAttributes(string $reportType): array
    {
        $baseReport = substr($reportType, 0, 3);
        $hasProDeputy = str_contains($reportType, '-5');
        $hasPaDeputy = str_contains($reportType, '-6');

        return [
            'baseReport' => $baseReport,
            'orderType' => match ($baseReport) {
                '102', '103' => 'pfa',
                '104' => 'hw'
            },
            'isHybrid' => str_contains($reportType, '-4'),
            'hasNonLayDeputy' => $hasProDeputy || $hasPaDeputy,
            'deputyType' => match (true) {
                $hasProDeputy => 'PRO',
                $hasPaDeputy => 'PA',
                default => 'LAY',
            }
        ];
    }
}
