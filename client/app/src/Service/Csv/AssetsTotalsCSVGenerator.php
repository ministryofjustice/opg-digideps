<?php

declare(strict_types=1);

namespace App\Service\Csv;

class AssetsTotalsCSVGenerator
{
    private CsvBuilder $csvBuilder;

    public function __construct(CsvBuilder $csvBuilder)
    {
        $this->csvBuilder = $csvBuilder;
    }

    public function generateAssetsTotalValuesCSV(array $assetsTotals): string
    {
        $headers = [
            'Lay - Liquid',
            'Lay - Non-Liquid',
            'Prof - Liquid',
            'Prof - Non-Liquid',
            'PA - Liquid',
            'PA - Non-Liquid',
            'Grand Total',
        ];

        $rows = [
            [
                $assetsTotals['lays']['liquid'],
                $assetsTotals['lays']['non-liquid'],
                $assetsTotals['profs']['liquid'],
                $assetsTotals['profs']['non-liquid'],
                $assetsTotals['pas']['liquid'],
                $assetsTotals['pas']['non-liquid'],
                $assetsTotals['grandTotal'],
            ],
        ];

        return $this->csvBuilder->buildCsv($headers, $rows);
    }
}
