<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\Audit;

use OPG\Digideps\Backend\Service\Audit\AuditEvents;
use OPG\Digideps\Backend\Service\Time\DateTimeProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AuditEventsTest extends TestCase
{
    private DateTimeProvider&MockObject $dateTimeProvider;
    private \DateTime $now;

    public function setUp(): void
    {
        $this->now = new \DateTime();
        $this->dateTimeProvider = self::createMock(DateTimeProvider::class);
        $this->dateTimeProvider->expects(self::once())
            ->method('getDateTime')
            ->willReturn($this->now);
    }

    #[Test]
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

        $actual = new AuditEvents($this->dateTimeProvider)->clientArchived(
            'USER_ARCHIVED_CLIENT',
            '12345678',
            new \DateTime('2023-01-01T00:00:00+00:00'),
            'me@test.com',
        );

        $this->assertEquals($expected, $actual);
    }
}
