<?php

declare(strict_types=1);

namespace App\Service\Audit;

use App\Service\Time\DateTimeProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AuditEventsTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|DateTimeProvider $dateTimeProvider;

    public function setUp(): void
    {
        $this->now = new \DateTime();
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->dateTimeProvider->getDateTime()->shouldBeCalled()->willReturn($this->now);
    }

    /**
     * @test
     */
    public function clientArchived(): void
    {
        $expected = [
            'trigger' => 'USER_ARCHIVED_CLIENT',
            'case_number' => '12345678',
            'archived_by' => 'me@test.com',
            'deputyship_start_date' => '2023-01-01T00:00:00+00:00',
            'archived_on' => $this->now->format(\DateTime::ATOM),
            'event' => 'CLIENT_ARCHIVED',
            'type' => 'audit',
        ];

        $actual = (new AuditEvents($this->dateTimeProvider->reveal()))->clientArchived(
            'USER_ARCHIVED_CLIENT',
            '12345678',
            new \DateTime('2023-01-01T00:00:00+00:00'),
            'me@test.com',
        );

        $this->assertEquals($expected, $actual);
    }
}
