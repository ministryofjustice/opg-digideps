<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Csv;

use OPG\Digideps\Frontend\Entity\Report\Satisfaction;
use OPG\Digideps\Frontend\Service\Csv\CsvBuilder;
use OPG\Digideps\Frontend\Service\Csv\SatisfactionCsvGenerator;
use PHPUnit\Framework\TestCase;

class SatisfactionCsvGeneratorTest extends TestCase
{
    public function testGenerateSatisfactionResponsesCsv(): void
    {
        $satisfactions = [
            new Satisfaction()
                ->setScore(5)
                ->setCreated(new \DateTime('2020-12-25'))
                ->setComments('Loved it')
                ->setDeputyRole('LAY_DEPUTY')
                ->setReportType('102'),
            new Satisfaction()
                ->setScore(2)
                ->setCreated(new \DateTime('2020-12-26'))
                ->setComments('Not great...')
                ->setDeputyRole('PROF_DEPUTY')
                ->setReportType('103'),
            new Satisfaction()
                ->setScore(3)
                ->setCreated(new \DateTime('2026-10-13'))
        ];

        $expectedCsv = <<<CSV
"Satisfaction Score",Comments,"Deputy Role","Report Type","Date Provided"
5,"Loved it",LAY_DEPUTY,102,2020-12-25
2,"Not great...",PROF_DEPUTY,103,2020-12-26
3,,,,2026-10-13

CSV;

        $sut = new SatisfactionCsvGenerator(new CsvBuilder());
        self::assertEquals($expectedCsv, $sut->generateSatisfactionResponsesCsv($satisfactions));
    }
}
