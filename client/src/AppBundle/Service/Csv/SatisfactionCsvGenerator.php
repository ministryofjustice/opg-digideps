<?php declare(strict_types=1);


namespace AppBundle\Service\Csv;

class SatisfactionCsvGenerator
{
    /**
     * @var CsvBuilder
     */
    private CsvBuilder $csvBuilder;

    public function __construct(CsvBuilder $csvBuilder)
    {
        $this->csvBuilder = $csvBuilder;
    }

    public function generateSatisfactionResponsesCsv()
    {
    }
}
