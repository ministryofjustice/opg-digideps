<?php

declare(strict_types=1);

namespace App\Service\Csv;

class SatisfactionCsvGenerator
{
    private CsvBuilder $csvBuilder;

    public function __construct(CsvBuilder $csvBuilder)
    {
        $this->csvBuilder = $csvBuilder;
    }

    public function generateSatisfactionResponsesCsv(array $satisfactions)
    {
        $headers = ['Satisfaction Score', 'Comments', 'Deputy Role', 'Report Type', 'Date Provided'];
        $rows = [];

        foreach ($satisfactions as $satisfaction) {
            $rows[] = [
                $satisfaction->getScore(),
                $satisfaction->getComments(),
                $satisfaction->getDeputyrole(),
                $satisfaction->getReporttype(),
                $satisfaction->getCreated()->format('Y-m-d'),
            ];
        }

        return $this->csvBuilder->buildCsv($headers, $rows);
    }
}
