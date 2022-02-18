<?php

declare(strict_types=1);

namespace App\Service\Csv;

use PHPUnit\Framework\TestCase;

class UserResearchResponseCsvGeneratorTest extends TestCase
{
    /** @test */
    public function generateUserResearchResponseCsv()
    {
        $urArray = [
            '0' => [
                'id' => 'f0c69ca2-883b-44ed-a182-87939b9d443d',
                'deputyshipLength' => 'underOne',
                'hasAccessToVideoCallDevice' => 1,
                'created' => [
                    'date' => '2022-02-17 14:41:52.000000',
                    'timezone_type' => 3,
                    'timezone' => 'Europe/London',
                ],
                'satisfaction' => [
                    'id' => '10012',
                    'score' => 5,
                    'comments' => 'Amazing service',
                    'deputyrole' => 'ROLE_LAY_DEPUTY',
                    'reporttype' => '102',
                    'created' => [
                        'date' => '2020-01-01 14:41:52.000000',
                        'timezone_type' => 3,
                        'timezone' => 'Europe/London',
                    ],
                ],
                'user' => [
                    'email' => 'test1@example.org',
                    'phoneMain' => '01211234567',
                 ],
                'researchType' => [
                    'id' => '317abaf1-33bb-44d7-abef-23fa0249ea4c',
                    'surveys' => null,
                    'videoCall' => null,
                    'phone' => 1,
                    'inPerson' => 1,
                ],
            ],
            '1' => [
                'id' => 'f0c69ca2-883b-44ed-a182-87939b9d443e',
                'deputyshipLength' => 'sixToTen',
                'hasAccessToVideoCallDevice' => 0,
                'created' => [
                    'date' => '2022-02-17 14:41:52.000000',
                    'timezone_type' => 3,
                    'timezone' => 'Europe/London',
                ],
                'satisfaction' => [
                    'id' => '10012',
                    'score' => 2,
                    'comments' => 'Not impressed',
                    'deputyrole' => 'ROLE_PROF_ADMIN',
                    'reporttype' => '103',
                    'created' => [
                        'date' => '2020-12-12 14:41:52.000000',
                        'timezone_type' => 3,
                        'timezone' => 'Europe/London',
                    ],
                ],
                'user' => [
                    'email' => 'test2@example.org',
                    'phoneMain' => '01217654321',
                ],
                'researchType' => [
                    'id' => '317abaf1-33bb-44d7-abef-23fa0249ea4c',
                    'surveys' => 1,
                    'videoCall' => 1,
                    'phone' => 1,
                    'inPerson' => 1,
                ],
            ],
        ];

        $expectedCsv = <<<CSV
"Satisfaction Score",Comments,"Deputy Role","Report Type","Date Provided","Deputyship Length Years","Agreed Research Types","Has Videocall Access",Email,"Phone Number"
5,"Amazing service",ROLE_LAY_DEPUTY,102,2020-01-01,"Less than 1","phone,inPerson",Yes,test1@example.org,01211234567
2,"Not impressed",ROLE_PROF_ADMIN,103,2020-12-12,"6 - 10","surveys,videoCall,phone,inPerson",No,test2@example.org,01217654321

CSV;

        $sut = new UserResearchResponseCsvGenerator(new CsvBuilder());
        self::assertEquals($expectedCsv, $sut->generateUserResearchResponsesCsv($urArray));
    }
}
