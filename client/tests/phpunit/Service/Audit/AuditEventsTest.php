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
            'event' => 'CLIENT_DISCHARGED',
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

    /**
     * @test
     * @dataProvider userEmailChangeProvider
     */
    public function userEmailChanged(string $name)
    {
        $now = new DateTime();
        /** @var ObjectProphecy|DateTimeProvider $dateTimeProvider */
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($now);

        $expected = [
            'trigger' => 'ADMIN_USER_EDIT',
            'email_changed_from' => 'me@test.com',
            'email_changed_to' => 'you@test.com',
            'full_name' => $name,
            'changed_on' => $now->format(DateTime::ATOM),
            'changed_by' => 'super-admin@email.com',
            'subject_role' => 'ROLE_LAY_DEPUTY',
            'event' => 'USER_EMAIL_CHANGED',
            'type' => 'audit'
        ];

        $actual = (new AuditEvents($dateTimeProvider->reveal()))->userEmailChanged(
            'ADMIN_USER_EDIT',
            'me@test.com',
            'you@test.com',
            $name,
            'super-admin@email.com',
            'ROLE_LAY_DEPUTY'
        );

        $this->assertEquals($expected, $actual);
    }

    public function userEmailChangeProvider()
    {
        return [
            'Panda Bear' => ['Panda Bear'],
            'Geologist' => ['Geologist']
        ];
    }

    /**
     * @test
     * @dataProvider roleChangedProvider
     */
    public function roleChanged(string $trigger, $changedFrom, $changedTo, $changedBy, $userChanged): void
    {
        $now = new DateTime();
        /** @var ObjectProphecy|DateTimeProvider $dateTimeProvider */
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($now);

        $expected = [
            'trigger' => $trigger,
            'role_changed_from' => $changedFrom,
            'role_changed_to' => $changedTo,
            'changed_by' => $changedBy,
            'user_changed' => $userChanged,
            'changed_on' => $now->format(DateTime::ATOM),
            'event' => AuditEvents::EVENT_ROLE_CHANGED,
            'type' => 'audit'
        ];

        $actual = (new AuditEvents($dateTimeProvider->reveal()))->roleChanged(
            $trigger,
            $changedFrom,
            $changedTo,
            $changedBy,
            $userChanged
        );

        $this->assertEquals($expected, $actual);
    }

    public function roleChangedProvider()
    {
        return [
            'PA to LAY' => ['ADMIN_BUTTON', 'ROLE_PA', 'ROLE_LAY_DEPUTY', 'polly.jean.harvey@test.com', 't.amos@test.com'],
            'PROF to PA' => ['ADMIN_BUTTON', 'ROLE_PROF', 'ROLE_PA', 't.amos@test.com', 'polly.jean.harvey@test.com'],
        ];
    }
}
