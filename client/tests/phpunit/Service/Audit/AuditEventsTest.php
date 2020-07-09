<?php declare(strict_types=1);

namespace AppBundle\Service\Audit;

use AppBundle\Service\Time\FakeClock;
use DateTime;
use PHPUnit\Framework\TestCase;

class AuditEventsTest extends TestCase
{
    /**
     * @test
     * @dataProvider startDateProvider
     */
    public function clientDischarged(?string $expectedStartDate, ?DateTime $startDate): void
    {
        $now = new DateTime();
        $fakeClock = new FakeClock($now);

        $expected = [
            'trigger' => 'ADMIN_BUTTON',
            'case_number' => '19348522',
            'discharged_by' => 'me@test.com',
            'deputy_name' => 'Bjork Gudmundsdottir',
            'discharged_on' => $now->format(DateTime::ATOM),
            'deputyship_start_date' => $expectedStartDate,
            'event' => AuditEvents::CLIENT_DISCHARGED,
            'type' => 'audit'
        ];

        $actual = (new AuditEvents($fakeClock))->clientDischarged(
            'ADMIN_BUTTON',
            '19348522',
            'me@test.com',
            'Bjork Gudmundsdottir',
            $startDate
        );

        $this->assertEquals($expected, $actual);
    }

    public function startDateProvider()
    {
         return [
             'Start date present' => [
                 '2019-07-08T09:36:00+01:00',
                 new DateTime('2019-07-08T09:36', new \DateTimeZone('+0100'))
             ],
             'Null start date' => [null, null]
         ];
    }
}
