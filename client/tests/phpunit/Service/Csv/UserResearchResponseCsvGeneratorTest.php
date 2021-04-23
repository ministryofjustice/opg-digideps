<?php declare(strict_types=1);


namespace App\Service\Csv;

use App\Entity\Report\Satisfaction;
use App\Entity\User;
use App\Entity\UserResearch\ResearchType;
use App\Entity\UserResearch\UserResearchResponse;
use App\TestHelpers\UserHelper;
use App\TestHelpers\UserResearchResponseHelper;
use DateTime;
use PHPUnit\Framework\TestCase;

class UserResearchResponseCsvGeneratorTest extends TestCase
{
    /** @test */
    public function generateUserResearchResponseCsv()
    {
        $user1 = (new User())
            ->setEmail('test1@non.com')
            ->setPhoneMain('01211234567');

        $user2 = (new User())
            ->setEmail('test2@non.com')
            ->setPhoneMain('01217654321');

        $satisfaction1 = (new Satisfaction())
            ->setComments('Amazing service')
            ->setCreated(new DateTime('01-01-2020'))
            ->setDeputyrole('ROLE_LAY_DEPUTY')
            ->setReporttype('102')
            ->setScore(5);

        $satisfaction2 = (new Satisfaction())
            ->setComments('Not impressed')
            ->setCreated(new DateTime('12-12-2020'))
            ->setDeputyrole('ROLE_PROF_ADMIN')
            ->setReporttype('103')
            ->setScore(2);

        $urResponses = [
            (new UserResearchResponse())
                ->setSatisfaction($satisfaction1)
                ->setDeputyshipLength('underOne')
                ->setResearchType(
                    (new ResearchType())
                        ->setPhone(true)
                        ->setInPerson(true)
                )
                ->setUser($user1)
                ->setHasAccessToVideoCallDevice(true)
                ->setCreated(new DateTime('01-01-2020')),
            (new UserResearchResponse())
                ->setSatisfaction($satisfaction2)
                ->setDeputyshipLength('sixToTen')
                ->setResearchType(
                    (new ResearchType())
                        ->setSurveys(true)
                        ->setVideoCall(true)
                        ->setInPerson(true)
                        ->setPhone(true)
                )
                ->setUser($user2)
                ->setHasAccessToVideoCallDevice(false)
                ->setCreated(new DateTime('12-12-2020')),
        ];

        $expectedCsv = <<<CSV
"Satisfaction Score",Comments,"Deputy Role","Report Type","Date Provided","Deputyship Length Years","Agreed Research Types","Has Videocall Access",Email,"Phone Number"
5,"Amazing service",ROLE_LAY_DEPUTY,102,2020-01-01,"Less than 1","phone,inPerson",Yes,test1@non.com,01211234567
2,"Not impressed",ROLE_PROF_ADMIN,103,2020-12-12,"6 - 10","surveys,videoCall,phone,inPerson",No,test2@non.com,01217654321

CSV;

        $sut = new UserResearchResponseCsvGenerator(new CsvBuilder());
        self::assertEquals($expectedCsv, $sut->generateUserResearchResponsesCsv($urResponses));
    }
}
