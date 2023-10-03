<?php declare(strict_types=1);


namespace App\Service\Csv;

use PHPUnit\Framework\TestCase;

class CsvBuilderTest extends TestCase
{
    /** @test */
    public function buildCsv()
    {
        $sut = new CsvBuilder();
        $headers = ['name', 'age', 'location'];
        $rows = [
            ['Caroline Polachek', '35', 'USA'],
            ['Aaron Pfenning', '37', 'USA']
        ];

        $expectedCsv = <<<CSV
name,age,location
"Caroline Polachek",35,USA
"Aaron Pfenning",37,USA

CSV;
        self::assertEquals($expectedCsv, $sut->buildCsv($headers, $rows));
    }
}
