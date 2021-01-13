<?php declare(strict_types=1);


namespace App\Service\Csv;

use App\Entity\Report\Satisfaction;
use PHPUnit\Framework\TestCase;

class SatisfactionCsvGeneratorTest extends TestCase
{
    /** @test */
    public function generateSatisfactionResponsesCsv()
    {
        $satisfactions = [
            (new Satisfaction())
            ->setComments('Loved it')
            ->setCreated(new \DateTime('2020-12-25'))
            ->setDeputyrole('LAY_DEPUTY')
            ->setReporttype('102')
            ->setScore(5),
            (new Satisfaction())
                ->setComments('Not great...')
                ->setCreated(new \DateTime('2020-12-26'))
                ->setDeputyrole('PROF_DEPUTY')
                ->setReporttype('103')
                ->setScore(2)
        ];

        $expectedCsv = <<<CSV
"Satisfaction Score",Comments,"Deputy Role","Report Type","Date Provided"
5,"Loved it",LAY_DEPUTY,102,2020-12-25
2,"Not great...",PROF_DEPUTY,103,2020-12-26

CSV;

        $sut = new SatisfactionCsvGenerator(new CsvBuilder());
        self::assertEquals($expectedCsv, $sut->generateSatisfactionResponsesCsv($satisfactions));
    }
}
