<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\EventSubscriber;

use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Event\UserDeletedEvent;
use OPG\Digideps\Frontend\EventSubscriber\UserDeletedSubscriber;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserDeletedSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [UserDeletedEvent::NAME => 'logEvent'],
            UserDeletedSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @dataProvider userProvider
     */
    public function testLogEvent(User $deletedUser, string $expectedEventName): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $dateTimeProvider = self::createMock(DateTimeProvider::class);

        $now = new \DateTime();
        $dateTimeProvider->expects(self::once())->method('getDateTime')->willReturn($now);

        $deletedBy = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $event = new UserDeletedEvent($deletedUser, $deletedBy, $trigger);

        $expectedEvent = [
            'trigger' => $trigger,
            'deleted_on' => $now->format(\DateTime::ATOM),
            'deleted_by' => $deletedBy->getEmail(),
            'subject_full_name' => $deletedUser->getFullName(),
            'subject_email' => $deletedUser->getEmail(),
            'subject_role' => $deletedUser->getRoleName(),
            'event' => $expectedEventName,
            'type' => 'audit',
        ];

        $logger->expects(self::once())->method('notice')->with('', $expectedEvent);

        new UserDeletedSubscriber($logger, $dateTimeProvider)->logEvent($event);
    }

    public static function userProvider(): array
    {
        $deletedUser = UserHelpers::createUser();

        return [
            'Admin User' => [(clone $deletedUser)->setRoleName('ROLE_ADMIN'), AuditEvents::EVENT_ADMIN_DELETED],
            'Super Admin User' => [(clone $deletedUser)->setRoleName('ROLE_SUPER_ADMIN'), AuditEvents::EVENT_ADMIN_DELETED],
            'Non-admin user' => [(clone $deletedUser)->setRoleName('ROLE_NOT_AN_ADMIN'), AuditEvents::EVENT_DEPUTY_DELETED],
        ];
    }
}
