<?php

declare(strict_types=1);

namespace App\Service\Csv;

class ReportImbalanceCsvGenerator
{
    const CSV_HEADERS = [
        'Deputy Type',
        'No Imbalance Reported',
        'Imbalance Reported',
        'Imbalance %',
        'Total Submitted'
    ];

    public function __construct(private CsvBuilder $csvBuilder)
    {}

    public function generateReportImbalanceCsv(array $data): string
    {
        foreach  ($data as $row) {
            $csvRows[] = [
                $row['deputy_type'],
                $row['no_imbalance'],
                $row['reported_imbalance'],
                $row['imbalance_percent'],
                $row['total'],
            ];
        }

        return $this->csvBuilder->buildCsv(self::CSV_HEADERS, $csvRows);
    }
}
