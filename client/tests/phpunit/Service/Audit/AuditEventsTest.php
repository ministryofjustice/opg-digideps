<?php declare(strict_types=1);

namespace AppBundle\Service\Audit;

use AppBundle\Service\Time\FakeClock;
use DateTime;
use PHPUnit\Framework\TestCase;

class AuditEventsTest extends TestCase
{
    /**
     * @test
     */
    public function clientDischarged(): void
    {
        $now = new DateTime();
        $fakeClock = new FakeClock($now);

        $expected = [
            'trigger' => 'ADMIN_BUTTON',
            'case_number' => '19348522',
            'discharged_by' => 'me@test.com',
            'deputy_name' => 'Bjork Gudmundsdottir',
            'discharged_on' => $now->format(DateTime::ATOM),
            'deputyship_start_date' => '2019-07-08T09:36:00+00:00',
            'event' => AuditEvents::CLIENT_DISCHARGED,
            'type' => 'audit'
        ];

        $actual = (new AuditEvents($fakeClock))->clientDischarged(
            'ADMIN_BUTTON',
            '19348522',
            'me@test.com',
            'Bjork Gudmundsdottir',
            new DateTime('2019-07-08T09:36')
        );

        $this->assertEquals($expected, $actual);
    }
}
