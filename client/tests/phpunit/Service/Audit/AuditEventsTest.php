<?php declare(strict_types=1);

namespace AppBundle\Service\Audit;

use AppBundle\Service\Time\DateTimeProvider;
use AppBundle\Service\Time\FakeClock;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class AuditEventsTest extends TestCase
{
    /**
     * @test
     * @dataProvider startDateProvider
     */
    public function clientDischarged(?string $expectedStartDate, ?DateTime $actualStartDate): void
    {
        $now = new DateTime();
        /** @var ObjectProphecy|DateTimeProvider $dateTimeProvider */
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($now);

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

        $actual = (new AuditEvents($dateTimeProvider->reveal()))->clientDischarged(
            'ADMIN_BUTTON',
            '19348522',
            'me@test.com',
            'Bjork Gudmundsdottir',
            $actualStartDate
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
