<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\OrgCreatedEvent;
use OPG\Digideps\Frontend\EventSubscriber\OrgCreatedSubscriber;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OrgCreatedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals([
            OrgCreatedEvent::NAME => 'auditLog',
        ], OrgCreatedSubscriber::getSubscribedEvents());
    }

    public function testAuditLog(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $dateTimeProvider = self::createMock(DateTimeProvider::class);

        $now = new \DateTime();
        $dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($now);
        $trigger = 'ADMIN_MANUAL_ORG_CREATION';

        $currentUser = UserHelpers::createSuperAdminUser();
        $organisation = [
            'id' => 83,
            'name' => 'Your Organisation',
            'email_identifier' => 'mccracken.com',
            'is_activated' => 'TRUE',
        ];

        $orgCreatedEvent = new OrgCreatedEvent($trigger, $currentUser, $organisation);

        $expectedEvent = [
            'trigger' => $trigger,
            'created_by' => $currentUser->getEmail(),
            'organisation_id' => $organisation['id'],
            'organisation_name' => $organisation['name'],
            'organisation_identifier' => $organisation['email_identifier'],
            'organisation_status' => $organisation['is_activated'],
            'created_on' => $now->format(\DateTime::ATOM),
            'event' => AuditEvents::EVENT_ORG_CREATED,
            'type' => 'audit',
        ];

        $logger->expects(self::once())->method('notice')->with('', $expectedEvent);

        new OrgCreatedSubscriber($logger, $dateTimeProvider)->auditLog($orgCreatedEvent);
    }
}
