<?php

declare(strict_types=1);

namespace Tests\App\EventListener;

use App\Event\OrgCreatedEvent;
use App\EventSubscriber\OrgCreatedSubscriber;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\UserHelpers;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class OrgCreatedSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getSubscribedEvents()
    {
        self::assertEquals([
            OrgCreatedEvent::NAME => 'auditLog',
        ], OrgCreatedSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function auditLog()
    {
        $logger = self::prophesize(LoggerInterface::class);
        $dateTimeProvider = self::prophesize(DateTimeProvider::class);

        $now = new DateTime();
        $dateTimeProvider->getDateTime()->willReturn($now);
        $trigger = 'ADMIN_MANUAL_ORG_CREATION';

        $sut = new OrgCreatedSubscriber($logger->reveal(), $dateTimeProvider->reveal());

        $currentUser = UserHelpers::createSuperAdminUser();
        $organisation =
            [
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
            'created_on' => $now->format(DateTime::ATOM),
            'event' => AuditEvents::EVENT_ORG_CREATED,
            'type' => 'audit',
        ];

        $logger->notice('', $expectedEvent)->shouldBeCalled();
        $sut->auditLog($orgCreatedEvent);
    }
}
