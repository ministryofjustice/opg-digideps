<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Event\OrgCreatedEvent;
use OPG\Digideps\Frontend\EventSubscriber\OrgCreatedSubscriber;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
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
