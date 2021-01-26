<?php


namespace App\Service\Csv;

use App\Entity\Client;
use App\Entity\User;
use App\TestHelpers\ClientHelpers;
use App\TestHelpers\ReportHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;

class ActiveLaysCsvGeneratorTest extends TestCase
{
    /** @test */
    public function generateActiveLaysCsv()
    {
        $lays = [
            (new User())
                ->setId(10)
                ->setFirstname('Rosa')
                ->setLastname('Walton')
                ->setEmail('r.walton@example.com')
                ->setPhoneMain('01212324545')
                ->setClients([(new Client())->setFirstname('Sophie')->setLastname('Xeon')])
                ->setNumberOfSubmittedReports(1)
                ->setRegistrationDate(new DateTime('2020-11-28')),
            (new User())
                ->setId(457)
                ->setFirstname('Jenny')
                ->setLastname('Hollingworth')
                ->setEmail('j.hollingworth@example.com')
                ->setPhoneMain('01216861212')
                ->setClients([(new Client())->setFirstname('Charlotte')->setLastname('Aitchison')])
                ->setNumberOfSubmittedReports(3)
                ->setRegistrationDate(new DateTime('2020-09-15'))
        ];

        $expectedCsv = <<<CSV
Id,"Deputy Full Name","Deputy Email","Deputy Phone Number","Reports Submitted","Registered On","Client Full Name"
10,"Rosa Walton",r.walton@example.com,01212324545,1,"28 November 2020","Sophie Xeon"
457,"Jenny Hollingworth",j.hollingworth@example.com,01216861212,3,"15 September 2020","Charlotte Aitchison"

CSV;

        $sut = new ActiveLaysCsvGenerator(new CsvBuilder());
        self::assertEquals($expectedCsv, $sut->generateActiveLaysCsv($lays));
    }
}
