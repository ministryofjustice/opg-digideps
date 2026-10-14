<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Csv;

use OPG\Digideps\Frontend\Entity\Report\Satisfaction;

readonly class SatisfactionCsvGenerator
{
    public function __construct(private CsvBuilder $csvBuilder)
    {
    }

    /**
     * @param array<Satisfaction> $satisfactions
     */
    public function generateSatisfactionResponsesCsv(array $satisfactions): string
    {
        $headers = ['Satisfaction Score', 'Comments', 'Deputy Role', 'Report Type', 'Date Provided'];
        $rows = [];

        foreach ($satisfactions as $satisfaction) {
            $rows[] = [
                $satisfaction->getScore(),
                $satisfaction->getComments(),
                $satisfaction->getDeputyRole(),
                $satisfaction->getReportType(),
                $satisfaction->getCreated()->format('Y-m-d'),
            ];
        }

        return $this->csvBuilder->buildCsv($headers, $rows);
    }
}
