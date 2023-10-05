<?php declare(strict_types=1);


namespace App\Service\Csv;

use PHPUnit\Framework\TestCase;

class ActiveLaysCsvGeneratorTest extends TestCase
{
    /** @test */
    public function generateActiveLaysCsv()
    {
        $laysData = [
            [
                'id' => 10,
                'user_first_name' => 'Rosa',
                'user_last_name' => 'Walton',
                'user_email' => 'r.walton@example.com',
                'user_phone_number' => '01212324545',
                'submitted_reports' => '1',
                'registration_date' => '2021-02-05 17:26:19',
                'client_first_name' => 'Sophie',
                'client_last_name' => 'Xeon',
            ],
            [
                'id' => 457,
                'user_first_name' => 'Jenny',
                'user_last_name' => 'Hollingworth',
                'user_email' => 'j.hollingworth@example.com',
                'user_phone_number' => '01216861212',
                'submitted_reports' => '3',
                'registration_date' => '2020-09-15 18:01:47',
                'client_first_name' => 'Charlotte',
                'client_last_name' => 'Aitchison',
            ],
        ];

        $expectedCsv = <<<CSV
Id,"Deputy Full Name","Deputy Email","Deputy Phone Number","Reports Submitted","Registered On","Client Full Name"
10,"Rosa Walton",r.walton@example.com,01212324545,1,"2021-02-05 17:26:19","Sophie Xeon"
457,"Jenny Hollingworth",j.hollingworth@example.com,01216861212,3,"2020-09-15 18:01:47","Charlotte Aitchison"

CSV;

        $sut = new ActiveLaysCsvGenerator(new CsvBuilder());
        self::assertEquals($expectedCsv, $sut->generateActiveLaysCsv($laysData));
    }
}
